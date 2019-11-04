<?php

namespace app\cigoadmin\middleware;

use Closure;

class TrimArgs
{
    public function handle($request, Closure $next)
    {
        $argsList = input();
//        $this->trimRequestArgs($argsList);

        return $next($request);
    }

    /**
     * 对参数去空值
     * @param $args
     */
    protected function trimRequestArgs($args)
    {
        if (!is_array($args)) {
            return;
        }
        foreach ($args as $key => $item) {
            if (is_string($item)) {
                $args[$key] = trim($item);
                //TODO
                if (empty($args[$key]))
                    unset($args[$key]);
            }
            $this->trimRequestArgs($item);
        }
    }
}

