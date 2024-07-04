# AF Web Snippets

A handy and easy-to-use collection of web snippets for PHP Laravel to boost your web development and save a lot of time. This package includes AJAX form saving and much more.

## Installation

To install the package, use Composer:

```sh
composer require artflow-studio/snippets
```



## Usage

Add the following line at the end of the <body> tag in your Blade template:
```sh
@stack('scripts')
```
And boom, you are done!
## Features

- Ajax Form Saving
```
 @AF_AjaxForm([
        'id' => 'id_of_the_form',
        'route' => 'route.of.the.form',
        'method' => 'post',
        'logType' => 'swal',
        'onSuccess' => [
            'log' => 'swal', // options: 'swal', 'alert', 'console'
            'reload' => true, // true or false
            'dtable' => 'myDataTable' // ID of the DataTable to reinitialize
        ],
    ])
```
- Unique ID
```
generateUniqueID(model::class, 'column_name') // The Column where the unique id is to be saved
```

## Authors

- [@RaHee554](https://www.github.com/rahee554)
