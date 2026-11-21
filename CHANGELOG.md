# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `strictJson` option on `ApiDataProvider` to preserve JSON types: only the root object is converted to an array,
  nested objects stay `stdClass` (a `{}` field is no longer confused with `[]`)
- `Group::submitValue()` accepts a `stdClass` value
- `ApiDataProvider` handles `PATCH` and `DELETE` requests, like `FormDataProvider`
- `ScalarValidator` and `ScalarConstraint`, rejecting non-scalar values (arrays, objects) submitted to a simple type

### Deprecated

- Constructing `ApiDataProvider` without `strictJson: true`; strict JSON will be the only behavior in v4
- `scalar` option of simple types: `false` disables the non-scalar check, `true` is redundant; the option will be
  removed in v4 and non-scalar values always rejected, use a `Collection` or a `RawType` instead

### Fixed

- `ApiDataProvider` ignores a scalar parsed body instead of throwing a `TypeError`

### Changed

- Simple types (`Text`, `Number`, `Date`, `Hidden`, ...) now reject non-scalar submitted values by default with a
  `ScalarConstraint`, and no longer hydrate them into the mapped object (previously a `TypeError` on typed setters or
  properties). `File` and `Choice` are not affected


## [3.3.2] - 2026-10-25

_No changes in this release._

## [3.3.1] - 2026-10-16

_No changes in this release._

## [3.3.0] - 2026-09-24

### Added

- `FormMapping(get:, set:)` strategy accepted as the `mapped` option, allowing full control over how an
  element reads from and writes to the mapped object (arbitrary internal target, nested properties, on-the-fly
  transformations). The public `name` stays decoupled from the internal mapping target.
- `ElementInterface::getMapping(): ?FormMapping` normalizing the `mapped` option (`false`/`string`/`true`/
  `FormMapping`) into a single strategy.

### Changed

- Mapping is now resolved uniformly through the `FormMapping` strategy in `TypeHydrator`, `TypeCollector`
  and `AbstractElement::getMapped()`. No behavior change for the existing `bool`/`string` forms of the
  `mapped` option.

## [3.2.1] - 2026-09-11

### Fixed

- Avoid "Array to string conversion" warning in `LengthValidator`, `IntervalValidator` and `FormatValidator` when the submitted value is not scalar (e.g. an array)

## [3.2.0] - 2026-07-02

_No changes in this release._

## [3.1.1] - 2026-06-10

_No changes in this release._

## [3.1.0] - 2026-05-18

### Fixed

- Enforce `max_elements` limits consistently in `Collection::setValue()` and `Collection::submitValue()`
- Avoid duplicate `[]` suffix in multiple file input names rendered by Twig
- Fix enum array transformation by converting each submitted value independently in `EnumTransformer::fromForm()`
- Rewind uploaded file stream after reading magic bytes in `FileFormatValidator::validate()` to prevent downstream code from missing the beginning of the stream

## [3.0.0] - 2026-02-19

The version 3.0.0 marks the transition of the Berlioz Framework to a **Monorepo** structure.

Why this move?

- **Consistency:** Ensures all Berlioz components are tested and released together, preventing version mismatches.
- **Maintenance:** Simplifies the management of issues and pull requests by centralizing them in one place.
- **Better CI/CD:** Global testing ensures that a change in one package never breaks another.
