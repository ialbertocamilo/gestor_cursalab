<?php

namespace App\Interfaces\MediaLibrary;

use DateTimeInterface;
use Illuminate\Support\Str;
use League\Flysystem\Adapter\AbstractAdapter;
use Spatie\MediaLibrary\Support\UrlGenerator\BaseUrlGenerator;


class CustomUrlGenerator extends BaseUrlGenerator
{
    public function getUrl(): string
    {
        // info('getUrl');
        $url = $this->getDisk()->url($this->getPathRelativeToRoot());

        // info($url);

        $url = $this->versionUrl($url);
        // info($url);

        $url = Str::replaceFirst(config('media-library.disk_bucket'), 'client-20-coldplay', $url);

        // info('-----------------');
        // info($url);
        // info('-----------------');
        return $url;
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        return $this->getDisk()->temporaryUrl($this->getPathRelativeToRoot(), $expiration, $options);
    }

    public function getBaseMediaDirectoryUrl()
    {
        info('getBaseMediaDirectoryUrl');
        $url = $this->getDisk()->url('/');
        $url = Str::replaceFirst(config('media-library.disk_bucket'), 'client-20-coldplay', $url);
        info($url);
        info('-----------------');

        return $url;
    }

    public function getPath(): string
    {
        $adapter = $this->getDisk()->getAdapter();
        // info((array)$adapter);

        $cachedAdapter = '\League\Flysystem\Cached\CachedAdapter';

        if ($adapter instanceof $cachedAdapter) {
            $adapter = $adapter->getAdapter();
        }

        $pathPrefix = '';
        if ($adapter instanceof AbstractAdapter) {
            $pathPrefix = $adapter->getPathPrefix();
        }

        // info($pathPrefix);
        // info($this->getPathRelativeToRoot());

        // info('-----------------');
        return $pathPrefix.$this->getPathRelativeToRoot();
    }

    public function getResponsiveImagesDirectoryUrl(): string
    {
        // info('getResponsiveImagesDirectoryUrl');
        $base = Str::finish($this->getBaseMediaDirectoryUrl(), '/');
        info($base);

        $path = $this->pathGenerator->getPathForResponsiveImages($this->media);
        // info($path);

        $url = Str::finish(url($base.$path), '/');

        $url = Str::replaceFirst(config('media-library.disk_bucket'), 'client-20-coldplay', $url);
        
        // info($url);

        // info('-----------------');
        return $url;
    }
}
