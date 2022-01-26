# 201Created  FileStore

This library provides file storage capabilities.

## Requirements
Command handlers and event subscribers must be enabled in the project. See 201created/ddd documentation for how to set this up.

## Installation
 
- Run `composer require becklyn/file-store` 
- Add the following to bundles.php:
```
Becklyn\FileStore\BecklynFileStoreBundle::class => ['all' => true],
```
- run `php bin/console doctrine:migrations:diff` to create a Doctrine migration for the file store. Check the migrations file and manually remove anything unrelated to the becklyn_files and becklyn_filesystem_file_pointers tables. Execute the migration by running `php bin/console doctrine:migrations:migrate`
- Add the following to services.yaml if the command handlers should log errors:
```
becklyn_file_store.handler.create_file:
        class: Becklyn\FileStore\Application\CreateFileHandler
        arguments:
            $logger: '@YOUR_PSR_LOGGER_INTERFACE_COMPLIANT_SERVICE_HERE'
        tags:
            - { name: command_handler, register_public_methods: true }

becklyn_file_store.handler.replace_file_contents:
    class: Becklyn\FileStore\Application\ReplaceFileContentsHandler
    arguments:
        $logger: '@YOUR_PSR_LOGGER_INTERFACE_COMPLIANT_SERVICE_HERE'
    tags:
        - { name: command_handler, register_public_methods: true }
```

## How To

Files are saved to the file store by dispatching CreateFileCommand and ReplaceFileContentsCommand through the command bus.
Files are read by using the load method of the FileManager class.
Files created by the library will be stored to %kernel_project_root%/var/becklyn-files folder by default. This can be changed through configuration.

## Configuration

To change the values of configuration options from their defaults, create a becklyn_file_store.yaml file in the config/packages folder with the following contents:
 ```
becklyn_file_store:
    option_name: value
    option_namespace_1:
        namespaced_option_name: value
 ```

### Available Options
 
#### filesystem.base_path
 
 - Type: string
 - Default: '%kernel_project_root%/var/becklyn-files'
 
 This is the folder where files will be saved to.