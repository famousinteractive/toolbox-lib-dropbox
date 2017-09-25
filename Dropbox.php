<?php

namespace App\Libraries\Famous\Dropbox;

use App\Libraries\Famous\HttpCommunicator\Communicator;

class Dropbox
{
    protected $_communicator = null;

    /**
     * Dropbox constructor.
     */
    public function __construct( )
    {
        $this->_communicator = new Communicator( env('DROPBOX_ACCESS_TOKEN'));
    }

    /**
     * @param string $directory
     * @return array
     */
    public function getListFolder( $directory)
    {
        return json_decode($this->_communicator->setJson()->post('2/files/list_folder', [
            'path' => $directory, //Camera uploads
            'recursive' => true,
            'include_media_info' => true,
        ])->getBody()->getContents(), true);
    }

    /**
     * @param string $cursor
     * @return array
     */
    public function getListFolderContinue( $cursor) {
        return json_decode($this->_communicator->setJson()->post('2/files/list_folder/continue', [
            'cursor' => $cursor,
        ])->getBody()->getContents(), true);
    }

    /**
     * @param string $path
     * @param string $format
     * @param string $size
     * @return string
     */
    public function getFileThumbnail( $path, $format = 'jpeg', $size = 'w64h64') {
        return $this->_communicator->setSubDomain('content')->setExtraHeaders(
            [
                'Dropbox-API-Arg'   => json_encode([
                    'path'      => $path,
                    'format'    => $format,
                    'size'      => $size
                ])
            ])
            ->setJson()->post('2/files/get_thumbnail', [])->getBody()->getContents();
    }

    /**
     * @param string $path
     * @return array
     */
    public function getTemporaryLink( $path): array {
        return \Cache::remember('dropbox.link.'.json_encode($path), 60*4, function() use($path) {
            return json_decode($this->_communicator->setJson()->post('2/files/get_temporary_link', [
                'path'      => $path,
            ])->getBody()->getContents(), true);
        });
    }

}