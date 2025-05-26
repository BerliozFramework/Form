# Berlioz Form

[![Latest Version](https://img.shields.io/packagist/v/berlioz/form.svg?style=flat-square)](https://github.com/BerliozFramework/Form/releases)
[![Software license](https://img.shields.io/github/license/BerliozFramework/Form.svg?style=flat-square)](https://github.com/BerliozFramework/Form/blob/main/LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/BerliozFramework/Form/tests.yml?branch=main&style=flat-square)](https://github.com/BerliozFramework/Form/actions/workflows/tests.yml?query=branch%3Amain)
[![Quality Grade](https://img.shields.io/codacy/grade/2580ecb12b404940b20b246f6350c11a/main.svg?style=flat-square)](https://app.codacy.com/gh/BerliozFramework/Form)
[![Total Downloads](https://img.shields.io/packagist/dt/berlioz/form.svg?style=flat-square)](https://packagist.org/packages/BerliozFramework/Form)

**Berlioz Form** is a PHP library to manage your forms.

## Installation

### Composer

You can install **Berlioz Form** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/form
```

### Dependencies

* **PHP** ^8.0
* Packages:
  * **berlioz/helpers**
  * **psr/http-message**

## Description

3 types of elements exists in **Berlioz Form**:

- `AbstractType`: it's a form control
- `Group`: represents an object in OOP
- `Collection`: represents a collection of AbstractType or Group

Input types available:

- `Button`
- `Checkbox`
- `Choice`
- `Date`
- `DateTime`
- `Email`
- `File`
- `Hidden`
- `Month`
- `Number`
- `Password`
- `Range`
- `Reset`
- `Search`
- `Submit`
- `Tel`
- `Text`
- `TextArea`
- `Time`
- `Url`
- `Week`

## Usage

### Form creation

Constructor of `Form` object accept 3 parameters:

- Name of form
- Mapped object
- Array of options

Example:

```php
$form = new Form('my_form', null, ['method' => 'post']);
```

### Declare form control

`add` method accept 3 parameters:

- Name of control (must be the same that the mapped element)
- Type (class name or object)
- Array of options

Options are different between controls.

Example:

```php
$form->add('my_control', Text::class, ['label' => 'My control']);
```

### Handle

**Berlioz Form** implements **PSR-7** (HTTP message interfaces). You must give the server request to the `handle`
method.

```php
$form = new Form('my_form', null, ['method' => 'post']);
// ...

$form->handle($request);

if ($form->isSubmitted() && $form->isValid()) {
    // ...
}
```

### Group

Example for an postal address:

```php
$addressGroup = new Group(['type' => 'address']);
$addressGroup
    ->add('address', Text::class, ['label' => 'Address'])
    ->add(
        'address_next',
        Text::class,
        ['label' => 'Address (next)',
         'required' => false]
    )
    ->add('postal_code', Text::class, ['label' => 'Postal code'])
    ->add('city', Text::class, ['label' => 'City']);

$form->add('address', $addressGroup);
```

### Collection

Example for a list of addresses:

```php
// Create group
$addressGroup = new Group(['type' => 'address']);
$addressGroup
    ->add('address', Text::class, ['label' => 'Address'])
    ->add(
        'address_next',
        Text::class,
        ['label' => 'Address (next)',
         'required' => false]
    )
    ->add('postal_code', Text::class, ['label' => 'Postal code'])
    ->add('city', Text::class, ['label' => 'City']);

// Create collection
$collection = new Collection([
    'prototype' => $addressGroup,
    'data_type' => ArrayObject::class
]);

// Add collection to form
$form->add('addresses', $collection);
```