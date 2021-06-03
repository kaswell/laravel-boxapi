<?php

namespace Kaswell\BoxApi;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class BoxApi
 * @package Kaswell\BoxApi
 */
class BoxApi extends ApiAbstract
{
    /**
     * @param string $name
     * @return string
     */
    private static function name(string $name): string
    {
        return Str::limit($name, 250, '...');
    }

    /**
     * @param string $name
     * @param string $parent_folder_id
     * @return array|\Illuminate\Http\Client\Response|\Illuminate\Support\Collection|mixed|object|string|void
     */
    public function createFolder(string $name, string $parent_folder_id = '0')
    {
        try {
            $this->setData([
                'name' => static::name($name),
                'parent' => [
                    'id' => $parent_folder_id,
                ]
            ]);
            $path = 'folders';
            $response = $this->send($path, POST_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $folder_id
     * @return array|object|void
     */
    public function getFolderList(string $folder_id = '0')
    {
        try {
            $path = 'folders/' . $folder_id . '/items';
            $response = $this->send($path, GET_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $folder_id
     * @return array|object|void
     */
    public function getFolderInfo(string $folder_id = '0')
    {
        try {
            $path = 'folders/' . $folder_id;
            $response = $this->send($path, GET_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $folder_id
     * @param array $data
     * @return array|object|void
     */
    public function updateFolder(string $folder_id, array $data = [])
    {
        try {
            if (Arr::has($data, 'name'))
                $data['name'] = static::name($data['name']);

            $this->setData($data);
            $path = 'folders/' . $folder_id;
            $response = $this->send($path, PUT_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $folder_id
     * @param string $name
     * @return array|object|void
     */
    public function renameFolder(string $folder_id, string $name)
    {
        try {
            $this->setData([
                'name' => static::name($name),
            ]);
            $response = $this->updateFolder($folder_id);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $folder_id
     * @param string $parent_folder_id
     * @return array|object|void
     */
    public function replaceFolder(string $folder_id, string $parent_folder_id = '0')
    {
        try {
            $this->setData([
                'parent' => [
                    'id' => $parent_folder_id,
                ],
            ]);
            $response = $this->updateFolder($folder_id);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $folder_id
     * @param bool $recursive
     * @return array|object|void
     */
    public function deleteFolder(string $folder_id, bool $recursive = true)
    {
        try {
            $folder_entries = $this->getFolderList($folder_id);
            if (isset($folder_entries['total_count']) && $folder_entries['total_count'] > 0) {
                foreach ($folder_entries['entries'] as $entry) {
                    if ($entry['type'] === 'file') {
                        $this->deleteFile($entry['id']);
                    }
                    if ($entry['type'] === 'folder') {
                        $this->deleteFolder($entry['id']);
                    }
                }
            }
            $path = 'folders/' . $folder_id . '?recursive=' . $recursive;
            $response = $this->send($path, DELETE_METHOD)->response(AS_IT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $folder_id
     * @return array|object|void
     */
    public function getFolderCollaborations(string $folder_id)
    {
        try {
            $path = 'folders/' . $folder_id . 'collaborations';
            $response = $this->send($path, GET_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $folder_id
     * @param string $user_email
     * @param string $role
     * @return array|object|void
     */
    public function createFolderCollaborations(string $folder_id, string $user_email, string $role = ROLE_VIEWER_UPLOADER)
    {
        try {
            $this->setData([
                'item' => [
                    'id' => $folder_id,
                    'type' => 'folder'
                ],
                'accessible_by' => [
                    "type" => "user",
                    'login' => $user_email
                ],
                'role' => $role
            ]);
            $path = 'collaborations';
            $response = $this->send($path, POST_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $collaboration_id
     * @param string $role
     * @return array|object|void
     */
    public function updateCollaborations(string $collaboration_id, string $role = ROLE_VIEWER_UPLOADER)
    {
        try {
            $this->setData([
                'role' => $role
            ]);
            $path = 'collaborations/' . $collaboration_id;
            $response = $this->send($path, PUT_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $collaboration_id
     * @return array|object|void
     */
    public function deleteFolderCollaborations(string $collaboration_id)
    {
        try {
            $path = 'collaborations/' . $collaboration_id;
            $response = $this->send($path, DELETE_METHOD)->response(AS_IT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $file_id
     * @return array|object|void
     */
    public function getFileInfo(string $file_id)
    {
        try {
            $path = 'files/' . $file_id;
            $response = $this->send($path, GET_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $filepath
     * @param string $name
     * @param string $parent_folder_id
     * @return array|object|void
     */
    public function uploadFile(string $filepath, string $name, string $parent_folder_id = '0')
    {
        try {
            $this->setData([
                'attributes' => [
                    'name' => $name,
                    'parent' => [
                        'id' => $parent_folder_id
                    ]
                ],
                'file' => new \CurlFile($filepath, mime_content_type($filepath), $name)
            ]);
            $path = 'files/content';
            $response = $this->multipart()->send(POST_METHOD, $path)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $file_id
     * @return array|object|void
     */
    public function deleteFile(string $file_id)
    {
        try {
            $path = 'files/' . $file_id;
            $response = $this->send($path, DELETE_METHOD)->response(AS_IT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }

    /**
     * @param string $user_id
     * @return array|object|void
     */
    public function getUser(string $user_id = 'me')
    {
        try {
            $path = 'users/' . $user_id;
            $response = $this->send($path, GET_METHOD)->response(AS_OBJECT);
        } catch (Exception $exception) {
            $this->setErrors($exception);
            return;
        }
        return $response;
    }
}