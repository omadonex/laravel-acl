<?php

namespace Omadonex\LaravelAcl\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Omadonex\LaravelSupport\Traits\TranslateResourceTrait;

class RoleResource extends Resource
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
            'isRoot' => $this->is_root,
            'isStaff' => $this->is_staff,
        ], $this->getTranslateIfLoaded(RoleTranslateResource::class, false));
    }
}
