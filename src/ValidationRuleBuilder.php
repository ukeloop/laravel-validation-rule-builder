<?php

namespace Ukeloop\ValidationRuleBuilder;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Ukeloop\ValidationRuleBuilder\Exceptions\NotFoundValidationRuleException;

/**
 * @method $this accepted()
 * @method $this acceptedIf(string $anotherfield, ...$values)
 * @method $this after($date)
 * @method $this afterOrEqual($date)
 * @method $this alpha()
 * @method $this alpha_dash()
 * @method $this alpha_num()
 * @method $this array()
 * @method $this bail()
 * @method $this before($date)
 * @method $this beforeOrEqual($date)
 * @method $this between($min, $max)
 * @method $this boolean()
 * @method $this confirmed()
 * @method $this current_password()
 * @method $this date()
 * @method $this dateEquals($date)
 * @method $this dateFormat($format)
 * @method $this declined()
 * @method $this declinedIf(string $anotherfield, ...$values)
 * @method $this different($field)
 * @method $this digits($value)
 * @method $this digitsBetween($min, $max)
 * @method $this dimensions(...$values)
 * @method $this distinct($values)
 * @method $this doesntStartWith(...$values)
 * @method $this doesntEndWith(...$values)
 * @method $this email(...$values)
 * @method $this endWith(...$values)
 * @method $this exclude($enum)
 * @method $this excludeIf(string $anotherfield, ...$values)
 * @method $this excludeUnless(string $anotherfield, ...$values)
 * @method $this excludeWith(string $anotherfield)
 * @method $this excludeWithout(string $anotherfield)
 * @method $this exists(string $table, ?string $column)
 * @method $this file()
 * @method $this filled()
 * @method $this gt(string $field)
 * @method $this gte(string $field)
 * @method $this image()
 * @method $this in(...$values)
 * @method $this inArray($anotherfield)
 * @method $this integer()
 * @method $this ip()
 * @method $this ipv4()
 * @method $this ipv6()
 * @method $this json()
 * @method $this lt(string $field)
 * @method $this lte(string $field)
 * @method $this macAddress()
 * @method $this max($value)
 * @method $this maxDigits($value)
 * @method $this mimetypes(string ...$value)
 * @method $this mimes(string ...$value)
 * @method $this min($value)
 * @method $this minDigits($value)
 * @method $this multipleOf($value)
 * @method $this noIn(string ...$value)
 * @method $this noRegex(string $pattern)
 * @method $this nullable()
 * @method $this numeric()
 * @method $this password()
 * @method $this present()
 * @method $this prohibited()
 * @method $this prohibitedIf(string $anotherfield, ...$values)
 * @method $this prohibitedUnless(string $anotherfield, ...$values)
 * @method $this prohibits(string ...$anotherfield)
 * @method $this regex(string $pattern)
 * @method $this required()
 * @method $this requiredIf(string $anotherfield, ...$values)
 * @method $this requiredUnless(string $anotherfield, ...$values)
 * @method $this requiredWith(...$values)
 * @method $this requiredWithAll(...$values)
 * @method $this requiredWithout(...$values)
 * @method $this requiredArrayKeys(...$values)
 * @method $this same(string $field)
 * @method $this size($value)
 * @method $this startWith(...$values)
 * @method $this string()
 * @method $this timezone()
 * @method $this unique(string $table, string $column)
 * @method $this url()
 * @method $this uuid()
 */
class ValidationRuleBuilder implements Rule, DataAwareRule, ValidatorAwareRule
{
    /**
     * The validator performing the validation.
     *
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    /**
     * The data under validation.
     *
     * @var array
     */
    protected $data;

    protected array $rules = [];

    protected array $messages = [];

    public function __construct(mixed ...$rules)
    {
        if (!empty($rules)) {
            $this->add(...$rules);
        }
    }

    /**
     * Set the performing validator.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the data under validation.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->messages = [];

        $validator = Validator::make(
            $this->data,
            [$attribute => $this->rules],
        );

        if ($validator->fails()) {
            return $this->fail($validator->messages()->all());
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return array
     */
    public function message()
    {
        return $this->messages;
    }

    /**
     * @return false
     */
    protected function fail(array|string $messages): bool
    {
        $messages = collect(Arr::wrap($messages))->map(function ($message) {
            return method_exists($this->validator, 'getTranslator')
                ? $this->validator->getTranslator()->get($message)
                : $message;
        })->all();

        $this->messages = array_merge($this->messages, $messages);

        return false;
    }

    public function add(mixed ...$rules): static
    {
        $newRules = collect(Arr::whereNotNull($rules))
            ->map(static function ($rule) {
                return \is_string($rule) ? explode('|', $rule) : $rule;
            })
            ->flatten()
            ->toArray()
        ;

        $this->rules = [
            ...$this->rules,
            ...$newRules,
        ];

        return $this;
    }

    public function __call(string $methodName, array $arguments): static
    {
        $rule = Str::snake($methodName);

        // TODO: Check existing the rule
        // if (0) {
        //     throw new NotFoundValidationRuleException(sprintf('The rule "%s" is not found.', $rule));
        // }

        if (!empty($arguments)) {
            $rule = sprintf('%s:%s', $rule, implode(',', $arguments));
        }

        return $this->add([$rule]);
    }
}
