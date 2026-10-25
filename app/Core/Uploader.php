<?php
class Uploader
{
    // The ONLY types we accept: real file type => the extension we save with
    private const IMAGES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public static function dir(): string
    {
        return ROOT . '/public/uploads';
    }

    /** @return array [file name or null, error message or null] */
    public static function image(array $file, int $maxMb = 2): array
    {
        $code = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($code === UPLOAD_ERR_NO_FILE) {
            return [null, null];   // no file chosen: that's fine, the photo is optional
        }
        if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE) {
            return [null, "The photo is too big. Maximum {$maxMb} MB."];
        }
        if ($code !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            return [null, 'Upload failed. Please try again.'];
        }
        if ($file['size'] > $maxMb * 1024 * 1024) {
            return [null, "The photo is too big. Maximum {$maxMb} MB."];
        }

        // Real file type: finfo looks INSIDE the file. A name or extension can be faked
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::IMAGES[$mime])) {
            return [null, 'Only JPG, PNG or WEBP images are allowed.'];
        }

        // Second check: a real image has a size. A fake one doesn't
        if (@getimagesize($file['tmp_name']) === false) {
            return [null, 'This file is not a valid image.'];
        }

        // Random name: the visitor's own file name is NEVER used (no overwriting, no tricks)
        $name = bin2hex(random_bytes(8)) . '.' . self::IMAGES[$mime];
        $dir  = self::dir();

        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return [null, 'Could not save the photo.'];
        }
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            return [null, 'Could not save the photo.'];
        }
        return [$name, null];
    }

    // Removes a saved photo from the disk (only names that look like OUR random names)
    public static function delete(?string $name): void
    {
        if (!$name || !preg_match('/^[a-f0-9]{16}\.(jpg|png|webp)$/', $name)) {
            return;
        }
        $path = self::dir() . '/' . $name;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}