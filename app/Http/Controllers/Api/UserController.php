<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Update the authenticated user's profile image.
     * Accepts multipart form with field 'image'.
     */
    public function updateProfileImage(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Early detect low-level PHP upload errors and return a friendly message.
        $uploadErr = $this->detectUploadErrorMessage('image');
        if ($uploadErr !== null) {
            Log::warning('[UserController::updateProfileImage] upload error', [
                'user_id' => $user->id,
                'error' => $uploadErr,
                // include $_FILES diagnostics in dev only
                'files' => app()->environment('production') ? null : ($_FILES['image'] ?? null),
            ]);
            return response()->json(['status' => 'error', 'message' => $uploadErr], 422);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'image.max' => 'Image too large. Maximum allowed size is 5 MB.',
            'image.mimes' => 'Only JPG and PNG images are allowed.',
        ]);

        // delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            try {
                Storage::disk('public')->delete($user->profile_image);
            } catch (\Throwable $e) {
                Log::warning('[UserController::updateProfileImage] failed deleting old image', [
                    'user_id' => $user->id,
                    'path' => $user->profile_image,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // store new image
        $path = $request->file('image')->store('profile_images', 'public');

        $user->profile_image = $path;
        $user->save();

        // If your User model has an accessor profile_image_url, return it; otherwise build URL.
        $profileUrl = null;
        try {
            $profileUrl = $user->profile_image_url ?? ($path ? url('storage/' . ltrim($path, '/')) : null);
        } catch (\Throwable $e) {
            $profileUrl = $path ? url('storage/' . ltrim($path, '/')) : null;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile image updated successfully',
            'profile_image_url' => $profileUrl,
        ]);
    }

    /**
     * Detect low-level PHP upload errors for a given field and return user-friendly message or null.
     * Returns null when no error is detected (UPLOAD_ERR_OK) or if the file key is not present.
     */
    private function detectUploadErrorMessage(string $field): ?string
    {
        if (!isset($_FILES[$field])) {
            // no file uploaded at PHP level; the validator will handle 'required'
            return null;
        }

        $err = isset($_FILES[$field]['error']) ? intval($_FILES[$field]['error']) : UPLOAD_ERR_OK;
        if ($err === UPLOAD_ERR_OK) return null;

        $map = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the server\'s maximum allowed size. Increase upload_max_filesize/post_max_size or upload a smaller file.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the form maximum size.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server misconfiguration: missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server error: failed to write uploaded file.',
            UPLOAD_ERR_EXTENSION  => 'File upload stopped by a PHP extension.',
        ];

        return $map[$err] ?? 'Unknown upload error (code: ' . $err . ').';
    }
}
