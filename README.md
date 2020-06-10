# 201Created  FileStore

This library provides file storage capabilities.

## Requirements
Command handlers and event subscribers must be enabled in the project. See 201created/ddd documentation for how to set this up.

## Installation
 
- Add the following to composer.json:
 ```
 "repositories": [
     {
         "type": "vcs",
         "url": "git@bitbucket.org:c201/201created-file-store.git"
     }
 ]
```
- Run `composer require 201created/file-store` 
- Add the following to bundles.php:
```
C201\FileStore\C201FileStoreBundle::class => ['all' => true],
```
- run `php bin/console doctrine:migrations:diff` to create a Doctrine migration for the file store. Check the migrations file and manually remove anything unrelated to the c201_files and c201_filesystem_file_pointers tables. Execute the migration by running `php bin/console doctrine:migrations:migrate`
- Add the following to services.yaml if the command handlers should log errors:
```
c201_file_store.handler.create_file:
        class: C201\FileStore\Application\CreateFileHandler
        arguments:
            $logger: '@YOUR_PSR_LOGGER_INTERFACE_COMPLIANT_SERVICE_HERE'
        tags:
            - { name: command_handler, register_public_methods: true }

c201_file_store.handler.replace_file_contents:
    class: C201\FileStore\Application\ReplaceFileContentsHandler
    arguments:
        $logger: '@YOUR_PSR_LOGGER_INTERFACE_COMPLIANT_SERVICE_HERE'
    tags:
        - { name: command_handler, register_public_methods: true }
```

## How To

Files are saved to the file store by dispatching CreateFileCommand and ReplaceFileContentsCommand through the command bus.
Files are read by using the load method of the FileManager class.
Files created by the library will be stored to %kernel_project_root%/var/c201-files folder by default. This can be changed through configuration.

## Configuration

To change the values of configuration options from their defaults, create a c201_file_store.yaml file in the config/packages folder with the following contents:
 ```
c201_file_store:
    option_name: value
    option_namespace_1:
        namespaced_option_name: value
 ```

### Available Options
 
#### filesystem.base_path
 
 - Type: string
 - Default: '%kernel_project_root%/var/c201-files'
 
 This is the folder where files will be saved to.