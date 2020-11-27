# flysystem-v2-simple-cache-adapter

[![Build Status](https://travis-ci.com/Lustmored/flysystem-v2-simple-cache-adapter.svg?branch=main)](https://travis-ci.com/Lustmored/flysystem-v2-simple-cache-adapter)

Simple cache decorator for Flysystem v2 Adapters.

# Installation

`composer require lustmored/flysystem-v2-simple-cache-adapter`

# Usage

CacheAdapter takes simply 2 parameters:

* `$adapter` - Filesystem adapter to be decorated
* `$cachePool` - PSR-6 compliant cache pool object

# Architecture

Please note this library is in early stages and cache format or functionality may change. I've created it for my own project and needs.

Idea is from `flysystem-cached-driver`, but cache logic is rethought. Instead of one big cache (that is killing memory when you have tens of thousands of files) it stores items on per-file basis.

Therefore, at least for now, there is no gain on `listContents` method, yet potential huge gains for `fileExists` and metadata-related methods (especially on network/cloud filesystems, like S3).
