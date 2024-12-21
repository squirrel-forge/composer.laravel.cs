<?php

namespace SquirrelForge\Laravel\CoreSupport\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Config;
use SquirrelForge\Laravel\CoreSupport\Exceptions\DynamicDebugAlreadyActiveException;
use SquirrelForge\Laravel\CoreSupport\Exceptions\DynamicDebugActivatorOriginInvalidException;
use SquirrelForge\Laravel\CoreSupport\Exceptions\DynamicDebugInvalidActivatorsException;
use SquirrelForge\Laravel\CoreSupport\Exceptions\DynamicDebugUnknownActivatorException;
use Symfony\Component\HttpFoundation\IpUtils;
use function SquirrelForge\Laravel\CoreSupport\clientIp;

/**
 * Dynamic debug middleware.
 */
class DynamicDebug {

    /** @var bool $activated */
    protected bool $activated = false;

    /** @var null|string $origin */
    protected ?string $origin = null;

    /** @var array $config */
    protected array $config;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = config('sqf-cs.debug');
    }

    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws DynamicDebugUnknownActivatorException
     * @throws DynamicDebugInvalidActivatorsException
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check if debug is disabled
        if (!config('app.debug')) {

            // Requires configured activators
            if (!isset($this->config['use']) || !is_array($this->config['use'])) {
                throw new DynamicDebugInvalidActivatorsException('No valid activators defined');
            }

            // Loop activators
            foreach ($this->config['use'] as $name) {

                // Run available types
                if ($name instanceof Closure || is_string($name) && function_exists($name)) {
                    call_user_func($name, $request, $this);
                } else if (method_exists($this, 'activate_with_' . $name)) {
                    call_user_func([$this, 'activate_with_' . $name], $request);
                } else {
                    $info = (is_string($name) || is_numeric($name) ? '"' . $name . '"' : 'unknown') .
                        '[' . gettype($name) . ']';
                    throw new DynamicDebugUnknownActivatorException( 'Unknown activator: ' . $info );
                }

                // Until one activates
                if ($this->activated) {
                    Config::set('app.debug', true);
                    break;
                }
            }

            // Check internal debug logging
            if ($this->config['log']) {
                Log::info('sqf-cs::dynamic-debug(' . ($this->activated ? 'true' : 'false') . ')' .
                    ($this->activated ? ' origin "' . $this->origin . '"' : ''));
            }
        }

        // Continue
        return $next($request);
    }

    /**
     * Is activated status
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->activated;
    }

    /**
     * Activate debug mode
     * @param string $origin
     * @return void
     * @throws DynamicDebugAlreadyActiveException
     * @throws DynamicDebugActivatorOriginInvalidException
     */
    public function activate(string $origin): void
    {
        if (empty($origin)) {
            throw new DynamicDebugActivatorOriginInvalidException('Failed with invalid activation origin');
        }
        if ($this->activated) {
            throw new DynamicDebugAlreadyActiveException( 'Already activated by: ' . $this->origin );
        }
        $this->activated = true;
        $this->origin = $origin;
    }

    /**
     * Activate by ip match
     * @param Request $request
     * @return  void
     * @throws DynamicDebugActivatorOriginInvalidException
     * @throws DynamicDebugAlreadyActiveException
     */
    protected function activate_with_ip(Request $request): void
    {
        $ips = $this->config['ips'];
        if (!empty($ips) && in_array(clientIp($request), $ips)) {
            $this->activate('ip');
        }
    }

    /**
     * Activate by ip range match
     * @param Request $request
     * @return void
     * @throws DynamicDebugActivatorOriginInvalidException
     * @throws DynamicDebugAlreadyActiveException
     */
    protected function activate_with_range(Request $request): void
    {
        $ranges = $this->config['ranges'];
        if (!empty($ranges) && IpUtils::checkIp(clientIp($request), $ranges)) {
            $this->activate('range');
        }
    }

    /**
     * Activate by key and pass values
     * @param Request $request
     * @return void
     * @throws DynamicDebugActivatorOriginInvalidException
     * @throws DynamicDebugAlreadyActiveException
     */
    protected function activate_with_keypass(Request $request): void
    {
        $key = $this->config['key'];
        $pass = $this->config['pass'];
        $limit = $this->config['limit'];

        // If active get input and process
        if (!empty($key) && !empty($pass) && !empty($limit)) {
            $input_pass = $request->get($key);

            // Check if we need to create a debug cookie
            // or if the timestamped encoded cookie/value pair is set and valid
            if (!empty($input_pass) && $input_pass === $pass) {
                $this->activate('keypass');
                $this->createDebugCookie();
            } else if ($this->hasDebugCookie()) {
                $this->activate('cookie');
            }
        }
    }

    /**
     * Check for a debug cookie
     * @return bool
     */
    public function hasDebugCookie(): bool
    {
        $today = date($this->config['lifetime']);
        $key = $this->config['key'];
        $pass = $this->config['pass'];
        return Cookie::get(md5($today . $key)) === md5($today . $pass);
    }

    /**
     * Create a debug cookie
     * @return void
     */
    public function createDebugCookie(): void
    {
        $today = date($this->config['lifetime']);
        $key = $this->config['key'];
        $pass = $this->config['pass'];
        $limit = $this->config['limit'];
        Cookie::queue(md5($today . $key), md5($today . $pass), $limit);
    }
}
