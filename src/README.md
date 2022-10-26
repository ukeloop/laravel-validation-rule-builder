# Laravel Validation Rule Builder

## Install

Just run the composer require command from the Terminal:

```bash
$ composer require ukeloop/laravel-validation-rule-builder
```

## Validation Rule Builder

You can define validation rules with method chain pattern.

```php
use Illuminate\Support\Facades\Validator;
use Ukeloop\ValidationRuleBuilder\ValidationRuleBuilder;

Validator::make($input, [
    'title' => (new ValidationRuleBuilder())->required()->string()->max(255),
]);
```

You can use Laravel validation rules.

```php
use Ukeloop\ValidationRuleBuilder\ValidationRuleBuilder;

$validationRule = new ValidationRuleBuilder();

$validationRule->required();
$validationRule->sometime();
$validationRule->nullable();

$validationRule->string();
$validationRule->integer();
$validationRule->numeric();
$validationRule->date();
$validationRule->bool();

$validationRule->min(8);
$validationRule->max(255);

$validationRule->email('strict', 'dns');

// etc ...
```
For other validation rules, please refer to the [laravel docs](https://laravel.com/docs/9.x/validation#available-validation-rules).

### Using Rule Objects

```php
$validationRule->add(Password::min(8));
```

### Using Closures

```php
new ValidationRuleBuilder(
    function ($attribute, $value, $fail) {
        if ($value === 'foo') {
            $fail('The ' . $attribute . ' is invalid.');
        }
    }
);
```

## Validation Rule Sets

Validation rule sets are created by combining laravel validation rules.
This will help you can keep your project's validation rules more DRY.

e.g. Rule of email address

```php
use Ukeloop\ValidationRuleBuilder\RuleSet;

class EmailRuleSet extends RuleSet
{
    public static function rules(): ValidationRuleBuilder
    {
        return new ValidationRuleBuilder('email:strict,dns,spoof');
    }
}
```

```php
use Illuminate\Support\Facades\Validator;

Validator::make($input, [
    'email' => EmailRuleSet::rules()->somtime()->required(),
]);
```

e.g. Rule of password

```php
use Ukeloop\ValidationRuleBuilder\RuleSet;

class PasswordRuleSet extends RuleSet
{
    public static function rules(): ValidationRuleBuilder
    {
        return new ValidationRuleBuilder(
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
        );
    }
}
```

```php
use Illuminate\Support\Facades\Validator;

Validator::make($input, [
    'password' => PasswordRuleSet::rules()->required()->confirmed(),
]);
```

## Testing

```bash
$ composer test
```
