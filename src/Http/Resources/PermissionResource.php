<?php

namespace Omadonex\LaravelAcl\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Omadonex\LaravelLocale\Traits\TranslateResourceTrait;

class PermissionResource extends Resource
{
    use TranslateResourceTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => $this->id,
        ], $this->getTranslateIfLoaded(PermissionTranslateResource::class, false));
    }
}
