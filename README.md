# flysystem-v2-simple-cache-adapter

[![Build Status](https://travis-ci.com/Lustmored/flysystem-v2-simple-cache-adapter.svg?branch=main)](https://travis-ci.com/Lustmored/flysystem-v2-simple-cache-adapter)

Simple cache decorator for Flysystem v2 Adapters.

# Installation

`composer require lustmored/flysystem-v2-simple-cache-adapter`

# Usage

CacheAdapter takes simply 2 parameters:

* `$adapter` - Filesystem adapter to be decorated
* `$cachePool` - PSR-6 compliant cache pool object

Example configuration in Symfony with `flysystem-bundle` (thanks to @weaverryan for summarizing things up):

[OPTIONAL] Create Cache pool to use:
```yaml
# config/packages/cache.yaml
framework:
    cache:
        pools:
            cache.flysystem.psr6:
                adapter: cache.app
```

Configure service. Here `@flysystem.adapter.uploads_adapter` is Flysystem adapter (based on `flysystem-bundle` configuration for `uploads_adapter` storage):
```yaml
# config/services.yaml
services:
    # ...

    flysystem.cache.adapter:
        class: Lustmored\Flysystem\Cache\CacheAdapter
        arguments:
            $adapter: '@flysystem.adapter.uploads_adapter'
            $cachePool: '@cache.flysystem.psr6'
```

Finally, wire it up into `flysytem-bundle`:
```yaml
# config/packages/flysystem.yaml
flysystem:
    storages:
        # ... your other storages

        cached_public_uploads_storage:
            # point this at your service id from above
            adapter: 'flysystem.cache.adapter'
```

# Architecture

Please note this library is in early stages and cache format or functionality may change. I've created it for my own project and needs.

Idea is from `flysystem-cached-driver`, but cache logic is rethought. Instead of one big cache (that is killing memory when you have tens of thousands of files) it stores items on per-file basis.

Therefore, at least for now, there is no gain on `listContents` method, yet potential huge gains for `fileExists` and metadata-related methods (especially on network/cloud filesystems, like S3).

# Performance

Benchmarks run with Redis in Docker (via provided `docker-compose.yml`). S3 benchmarks require setting environment vars (for example by providing `.env.bench.local`). Cached benchmarks have cache warmed by calling `listContents`.

Below are reports from single benchmark run on devel machine as of 0.0.4. They show that caching metadata with the local adapter makes no sense, while using it for S3 brings potential huge benefits when querying existing files. `fileExists` benchmark randomly queries files with about 50% of them existing.

| benchmark        | subject                  | set | revs | iter | mem_peak    | time_rev         | comp_z_value | comp_deviation |
|------------------|--------------------------|-----|------|------|-------------|------------------|--------------|----------------|
| LocalBench       | benchCopyAndDelete       | 0   | 1    | 0    | 2,695,904b  | 9,757.000μs      | 0.00σ        | 0.00%          |
| LocalBench       | benchListContents        | 0   | 1    | 0    | 2,695,904b  | 1,925.000μs      | 0.00σ        | 0.00%          |
| LocalBench       | benchMove                | 0   | 1    | 0    | 2,695,896b  | 13,083.000μs     | 0.00σ        | 0.00%          |
| LocalBench       | benchRandomFileExists    | 0   | 1    | 0    | 2,695,904b  | 1,893.000μs      | 0.00σ        | 0.00%          |
| LocalBench       | benchRandomFileSize      | 0   | 1    | 0    | 2,695,904b  | 2,216.000μs      | 0.00σ        | 0.00%          |
| LocalBench       | benchRandomLastModified  | 0   | 1    | 0    | 2,695,904b  | 3,689.000μs      | 0.00σ        | 0.00%          |
| LocalBench       | benchRandomMimeType      | 0   | 1    | 0    | 3,609,408b  | 28,681.000μs     | 0.00σ        | 0.00%          |
| LocalBench       | benchRandomRead          | 0   | 1    | 0    | 2,695,896b  | 3,490.000μs      | 0.00σ        | 0.00%          |
| LocalBench       | benchRandomSetVisibility | 0   | 1    | 0    | 2,695,912b  | 2,958.000μs      | 0.00σ        | 0.00%          |
| LocalBench       | benchRandomVisibility    | 0   | 1    | 0    | 2,695,904b  | 2,866.000μs      | 0.00σ        | 0.00%          |
| LocalBench       | benchRandomWrite         | 0   | 1    | 0    | 2,695,904b  | 11,619.000μs     | 0.00σ        | 0.00%          |
| AwsBench         | benchCopyAndDelete       | 0   | 1    | 0    | 14,163,224b | 25,638,767.000μs | 0.00σ        | 0.00%          |
| AwsBench         | benchListContents        | 0   | 1    | 0    | 10,141,384b | 394,385.000μs    | 0.00σ        | 0.00%          |
| AwsBench         | benchMove                | 0   | 1    | 0    | 17,951,440b | 54,856,950.000μs | 0.00σ        | 0.00%          |
| AwsBench         | benchRandomFileExists    | 0   | 1    | 0    | 11,294,928b | 12,921,070.000μs | 0.00σ        | 0.00%          |
| AwsBench         | benchRandomFileSize      | 0   | 1    | 0    | 11,153,896b | 5,655,586.000μs  | 0.00σ        | 0.00%          |
| AwsBench         | benchRandomLastModified  | 0   | 1    | 0    | 11,153,896b | 5,901,532.000μs  | 0.00σ        | 0.00%          |
| AwsBench         | benchRandomMimeType      | 0   | 1    | 0    | 11,153,896b | 6,200,496.000μs  | 0.00σ        | 0.00%          |
| AwsBench         | benchRandomRead          | 0   | 1    | 0    | 11,098,640b | 5,975,538.000μs  | 0.00σ        | 0.00%          |
| AwsBench         | benchRandomSetVisibility | 0   | 1    | 0    | 11,279,472b | 4,980,556.000μs  | 0.00σ        | 0.00%          |
| AwsBench         | benchRandomVisibility    | 0   | 1    | 0    | 11,086,464b | 7,118,953.000μs  | 0.00σ        | 0.00%          |
| AwsBench         | benchRandomWrite         | 0   | 1    | 0    | 11,223,672b | 6,863,634.000μs  | 0.00σ        | 0.00%          |
| CachedLocalBench | benchCopyAndDelete       | 0   | 1    | 0    | 3,114,992b  | 66,819.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchListContents        | 0   | 1    | 0    | 3,114,944b  | 29,511.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchMove                | 0   | 1    | 0    | 3,114,984b  | 120,609.000μs    | 0.00σ        | 0.00%          |
| CachedLocalBench | benchRandomFileExists    | 0   | 1    | 0    | 3,114,992b  | 17,195.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchRandomFileSize      | 0   | 1    | 0    | 3,114,992b  | 22,410.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchRandomLastModified  | 0   | 1    | 0    | 3,114,992b  | 13,735.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchRandomMimeType      | 0   | 1    | 0    | 4,079,264b  | 74,065.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchRandomRead          | 0   | 1    | 0    | 3,114,984b  | 16,104.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchRandomSetVisibility | 0   | 1    | 0    | 3,115,000b  | 36,545.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchRandomVisibility    | 0   | 1    | 0    | 3,114,992b  | 19,188.000μs     | 0.00σ        | 0.00%          |
| CachedLocalBench | benchRandomWrite         | 0   | 1    | 0    | 3,114,992b  | 30,787.000μs     | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchCopyAndDelete       | 0   | 1    | 0    | 14,701,496b | 25,902,601.000μs | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchListContents        | 0   | 1    | 0    | 10,741,088b | 145,838.000μs    | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchMove                | 0   | 1    | 0    | 18,489,776b | 53,389,348.000μs | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchRandomFileExists    | 0   | 1    | 0    | 11,360,328b | 9,891,007.000μs  | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchRandomFileSize      | 0   | 1    | 0    | 10,494,808b | 17,082.000μs     | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchRandomLastModified  | 0   | 1    | 0    | 10,494,808b | 16,780.000μs     | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchRandomMimeType      | 0   | 1    | 0    | 11,362,408b | 4,023,242.000μs  | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchRandomRead          | 0   | 1    | 0    | 11,655,608b | 6,180,574.000μs  | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchRandomSetVisibility | 0   | 1    | 0    | 11,930,632b | 5,753,747.000μs  | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchRandomVisibility    | 0   | 1    | 0    | 11,319,584b | 3,708,345.000μs  | 0.00σ        | 0.00%          |
| CachedAwsBench   | benchRandomWrite         | 0   | 1    | 0    | 11,791,352b | 6,785,903.000μs  | 0.00σ        | 0.00%          |
|------------------|--------------------------|-----|------|------|-------------|------------------|--------------|----------------|
