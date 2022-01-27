# Becklyn DDD FileStore

This library provides file storage capabilities using the Becklyn DDD framework.

## Requirements
Command handlers and event subscribers must be enabled in the project. See becklyn/ddd-symfony-bridge documentation for how to set this up.

## Installation
 
- Run `composer require becklyn/ddd-file-store` 
- Add the following to bundles.php:
```
Becklyn\Ddd\FileStore\BecklynFileStoreBundle::class => ['all' => true],
```
- There is a doctrine migration provided. Execute it by running `php bin/console doctrine:migrations:migrate`
- Add the following to services.yaml if the command handlers should log errors:
```
becklyn_ddd.file_store.handler.create_file:
        class: Becklyn\Ddd\FileStore\Application\CreateFileHandler
        arguments:
            $logger: '@YOUR_PSR_LOGGER_INTERFACE_COMPLIANT_SERVICE_HERE'
        tags:
            - { name: command_handler, register_public_methods: true }

becklyn_ddd.file_store.handler.replace_file_contents:
    class: Becklyn\Ddd\FileStore\Application\ReplaceFileContentsHandler
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

To change the values of configuration options from their defaults, create a becklyn_ddd.file_store.yaml file in the config/packages folder with the following contents:
 ```
becklyn_ddd.file_store:
    option_name: value
    option_namespace_1:
        namespaced_option_name: value
 ```

### Available Options
 
#### filesystem.base_path
 
 - Type: string
 - Default: '%kernel_project_root%/var/becklyn-files'
 
 This is the folder where files will be saved to.