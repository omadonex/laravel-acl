<?php

namespace Omadonex\LaravelAcl\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionTranslateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'lang' => $this->lang,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
