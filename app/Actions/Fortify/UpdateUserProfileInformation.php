<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);

            return;
        }

        $user->forceFill($this->profileAttributes($user, $input))->save();
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill($this->profileAttributes($user, $input) + [
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }

    /**
     * Build the profile attributes, storing a new avatar if provided.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function profileAttributes(User $user, array $input): array
    {
        $attributes = [
            'name' => $input['name'],
            'email' => $input['email'],
        ];

        if (filter_var($input['remove_avatar'] ?? false, FILTER_VALIDATE_BOOLEAN) && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $attributes['avatar'] = null;
        }

        if (($input['avatar'] ?? null) instanceof UploadedFile) {
            $attributes['avatar'] = $this->storeAvatar($user, $input['avatar']);
        }

        return $attributes;
    }

    protected function storeAvatar(User $user, UploadedFile $avatar): string
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        return $avatar->store('avatars', 'public');
    }
}
