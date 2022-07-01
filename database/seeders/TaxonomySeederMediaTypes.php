<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederMediaTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /*$extensiones = [
            'image' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'],
            'office' => ['xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx'],
            'video' => ['mp4', 'webm', 'mov',],
            'vimeo' => ['vimeo'],
            'audio' => ['mp3'],
            'pdf' => ['pdf'],
            'scorm' => ['scorm', 'zip'],
        ];*/

        $images = Taxonomy::create([
            'group' => 'media',
            'type' => 'type',
            'code' => 'images',
            'name' => 'Imágenes',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'image',
            'code' => 'image/jpeg',
            'name' => 'JPEG',
            'active' => ACTIVE,
            'position' => 1,
            'parent_id' => $images->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'image',
            'code' => 'image/jpeg',
            'name' => 'JPG',
            'active' => ACTIVE,
            'position' => 2,
            'parent_id' => $images->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'image',
            'code' => 'image/png',
            'name' => 'PNG',
            'active' => ACTIVE,
            'position' => 3,
            'parent_id' => $images->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'image',
            'code' => 'image/gif',
            'name' => 'GIF',
            'active' => ACTIVE,
            'position' => 4,
            'parent_id' => $images->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'image',
            'code' => 'image/svg+xml',
            'name' => 'SVG',
            'active' => ACTIVE,
            'position' => 5,
            'parent_id' => $images->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'image',
            'code' => 'image/webp',
            'name' => 'WEBP',
            'active' => ACTIVE,
            'position' => 6,
            'parent_id' => $images->id
        ]);

        $office = Taxonomy::create([
            'group' => 'media',
            'type' => 'type',
            'code' => 'ms-office',
            'name' => 'Microsoft Office',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/xls',
            'name' => 'Excel (.xls)',
            'active' => ACTIVE,
            'position' => 1,
            'parent_id' => $office->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/xlsx',
            'name' => 'Excel (.xlsx)',
            'active' => ACTIVE,
            'position' => 2,
            'parent_id' => $office->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/ppt',
            'name' => 'Power Point (.ppt)',
            'active' => ACTIVE,
            'position' => 3,
            'parent_id' => $office->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/pptx',
            'name' => 'Power Point (.pptx)',
            'active' => ACTIVE,
            'position' => 4,
            'parent_id' => $office->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/doc',
            'name' => 'Microsoft Word (.doc)',
            'active' => ACTIVE,
            'position' => 5,
            'parent_id' => $office->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/docx',
            'name' => 'Microsoft Word (.docx)',
            'active' => ACTIVE,
            'position' => 5,
            'parent_id' => $office->id
        ]);

        $video = Taxonomy::create([
            'group' => 'media',
            'type' => 'type',
            'code' => 'video',
            'name' => 'Videos',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'video',
            'code' => 'video/webm',
            'name' => 'WEBM video',
            'active' => ACTIVE,
            'position' => 1,
            'parent_id' => $video->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'video',
            'code' => 'video/vimeo',
            'name' => 'Vimeo',
            'active' => ACTIVE,
            'position' => 2,
            'parent_id' => $video->id
        ]);

        $audio = Taxonomy::create([
            'group' => 'media',
            'type' => 'type',
            'code' => 'audio',
            'name' => 'Audios',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'audio',
            'code' => 'audio/mpeg',
            'name' => 'MP3 audio',
            'active' => ACTIVE,
            'position' => 1,
            'parent_id' => $audio->id
        ]);

        $pdf = Taxonomy::create([
            'group' => 'media',
            'type' => 'type',
            'code' => 'pdf',
            'name' => 'PDF',
            'active' => ACTIVE,
            'position' => 5,
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/pdf',
            'name' => 'PDF',
            'active' => ACTIVE,
            'position' => 1,
            'parent_id' => $pdf->id
        ]);

        $scorm = Taxonomy::create([
            'group' => 'media',
            'type' => 'type',
            'code' => 'scorm',
            'name' => 'SCORM',
            'active' => ACTIVE,
            'position' => 6,
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/scorm',
            'name' => 'SCORM (.scorm)',
            'active' => ACTIVE,
            'position' => 1,
            'parent_id' => $scorm->id
        ]);

        Taxonomy::create([
            'group' => 'mime',
            'type' => 'application',
            'code' => 'application/zip',
            'name' => 'SCORM (.zip)',
            'active' => ACTIVE,
            'position' => 2,
            'parent_id' => $scorm->id
        ]);

    }
}
