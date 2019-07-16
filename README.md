# Berlioz Form

**Berlioz Form** is a PHP library to manage your forms.


## Installation

### Composer

You can install **Berlioz Form** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/form
```

### Dependencies

* **PHP** >= 7.1
* Packages:
  * **berlioz/utils**
  * **psr/http-message**


## Description

3 types of elements exists in **Berlioz Form**:
- `AbstractType`: it's a form control
- `Group`: represents an object in OOP
- `Collection`: represents a collection of AbstractType or Group


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

**Berlioz Form** implements **PSR-7** (HTTP message interfaces). You must give the server request to the `handle` method.

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
$form->add('address',
           (new Group)
               ->add('address', Text::class, ['label' => 'Address'])
               ->add('address_next', Text::class, ['label' => 'Address (next)', 'required' => false])
               ->add('postal_code', Text::class, ['label' => 'Postal code'])
               ->add('city', Text::class, ['label' => 'City']));
```

### Collection

Example for a list of addresses:
```php
$form->add('addresses', (new Collection(['prototype' =>
                                             (new Group(['type' => 'address']))
                                                 ->add('address', Text::class, ['label' => 'Address'])
                                                 ->add('address_next',
                                                       Text::class,
                                                       ['label'    => 'Address (next)',
                                                        'required' => false])
                                                 ->add('postal_code', Text::class, ['label' => 'Postal code'])
                                                 ->add('city', Text::class, ['label' => 'City']),
                                         'data_type' => '\ArrayObject'])))
```