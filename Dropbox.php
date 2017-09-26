<?php

namespace App\Libraries\Famous\Dropbox;

use App\Libraries\Famous\HttpCommunicator\Communicator;

/**
 * Class Dropbox
 * @package App\Libraries\Famous\Dropbox
 */
class Dropbox
{
    protected $_communicator = null;

    /**
     * Dropbox constructor.
     */
    public function __construct( )
    {
        $this->_ApiCommunicator = new Communicator(
            'https://api.dropbox.com/',
            [
                'Authorization' => 'Bearer ' . env('DROPBOX_ACCESS_TOKEN')
            ]
        );
    }

    /**
     * List all documents in a folder. If the limit is reached, it return a cursor to use the pagination
     * @param string $directory
     * @return array
     */
    public function getListFolder( $directory)
    {
        return json_decode($this->_communicator->useJson()->post('2/files/list_folder', [
            'path' => $directory,
            'recursive' => true,
            'include_media_info' => true,
        ])->getBody()->getContents(), true);
    }

    /**
     * Use this method to get the list with the cursor.
     * @param string $cursor
     * @return array
     */
    public function getListFolderContinue( $cursor) {
        return json_decode($this->_communicator->useJson()->post('2/files/list_folder/continue', [
            'cursor' => $cursor,
        ])->getBody()->getContents(), true);
    }

    /**
     * @param string $path
     * @param string $format jpeg|png
     * @param string $size w32h32|w64h64|w128h128|w640h480|w1024h768
     * @return string
     */
    public function getFileThumbnail( $path, $format = 'jpeg', $size = 'w64h64') {

        $contentCommunicator = new Communicator(
            'https://content.dropbox.com/',
            [
                'Authorization' => 'Bearer ' . env('DROPBOX_ACCESS_TOKEN'),
                'Dropbox-API-Arg'   => json_encode([
                    'path'      => $path,
                    'format'    => $format,
                    'size'      => $size
                ])
            ]
        );

        return $contentCommunicator->useJson()->post('2/files/get_thumbnail', [] )->getBody()->getContents();
    }

    /**
     * Get the link to a file with is path.
     * The link is valid 4 hours so we cached it for this time.
     * @param string $path
     * @return array
     */
    public function getTemporaryLink( $path) {
        return \Cache::remember('dropbox.link.'.json_encode($path), 60*4, function() use($path) {
            return json_decode($this->_communicator->setJson()->post('2/files/get_temporary_link', [
                'path'      => $path,
            ])->getBody()->getContents(), true);
        });
    }

}