<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        $token = $this->createToken('auth_token')->plainTextToken;

        return [
            'name' => $this->name,
            'role' => $this->role->name,
            'email' => $this->email,
            'profilePicUrl' => $this->profile_pic_url,
            'accessToken' => $token,
        ];
    }
}
