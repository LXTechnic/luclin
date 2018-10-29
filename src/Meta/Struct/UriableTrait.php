<?php

namespace Luclin\Meta\Struct;

/**
 *
 * @author andares
 */
trait UriableTrait {

    abstract public static function uriPath(): string;

    public function toUri(string $scheme): string {
        return "$scheme:".static::uriPath()."?".http_build_query($this->all());
    }
}
