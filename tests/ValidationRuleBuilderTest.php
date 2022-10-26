<?php

namespace Ukeloop\ValidationRuleBuilder\Tests;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Ukeloop\ValidationRuleBuilder\ValidationRuleBuilder;

/**
 * @internal
 *
 * @coversNothing
 */
final class ValidationRuleBuilderTest extends TestCase
{
    public function testItCanPasses(): void
    {
        $validator = Validator::make(
            [
                'name' => 'alfred',
                'email' => 'alfred@example.com',
                'bio' => '',
                'password' => 'z%Q78bLn7^!DHC2M',
                'password_confirmation' => 'z%Q78bLn7^!DHC2M',
            ],
            [
                'name' => new ValidationRuleBuilder('required|string|max:255'),
                'email' => new ValidationRuleBuilder(['required', 'email:strict']),
                'bio' => new ValidationRuleBuilder('nullable', 'string'),
                'password' => new ValidationRuleBuilder('nullable', Password::min(8), 'confirmed'),
            ],
        );

        static::assertTrue($validator->passes());
    }

    public function testItCanUseWithMethodChainPattern(): void
    {
        $validator = Validator::make(
            [
                'title' => '1234',
                'slug' => 1,
                'body' => '1234567890',
            ],
            [
                'title' => (new ValidationRuleBuilder())->required()->string()->min(5),
                'slug' => (new ValidationRuleBuilder())->required()->string()->min(3),
                'body' => (new ValidationRuleBuilder())->required()->string()->min(5),
            ],
        );

        static::assertTrue($validator->fails());

        static::assertCount(3, $validator->errors());

        static::assertTrue($validator->errors()->has('title'));
        static::assertCount(1, $validator->errors()->get('title'));

        static::assertTrue($validator->errors()->has('slug'));
        static::assertCount(2, $validator->errors()->get('slug'));

        static::assertFalse($validator->errors()->has('body'));
    }

    public function testItCanFails(): void
    {
        $validator = Validator::make(
            [
                'name' => null,
                'email' => 'invalid.@example.com',
                'bio' => '',
                'password' => 'z%Q78bLn7^!DHC2M',
                'password_confirmation' => 'invalid password',
            ],
            [
                'name' => new ValidationRuleBuilder('required|string|max:255'),
                'email' => new ValidationRuleBuilder(['required', 'email:strict']),
                'bio' => new ValidationRuleBuilder('nullable', 'string'),
                'password' => new ValidationRuleBuilder('nullable', Password::min(8), 'confirmed'),
            ],
        );

        static::assertTrue($validator->fails());

        static::assertCount(3, $validator->errors());

        static::assertTrue($validator->errors()->has('name'));
        static::assertTrue($validator->errors()->has('email'));
        static::assertFalse($validator->errors()->has('bio'));
        static::assertTrue($validator->errors()->has('password'));
    }

    public function testItCanRuleObjects(): void
    {
        $validator = Validator::make(
            [
                'current_password' => '12345',
                'new_password' => '1234567890',
            ],
            [
                'current_password' => new ValidationRuleBuilder(Password::min(8)),
                'new_password' => new ValidationRuleBuilder(Password::min(8)),
            ],
        );

        static::assertTrue($validator->fails());

        static::assertCount(1, $validator->errors());

        static::assertTrue($validator->errors()->has('current_password'));
        static::assertFalse($validator->errors()->has('new_password'));
    }

    public function testItCanUseClosures(): void
    {
        $validator = Validator::make(
            [
                'title' => 'foo',
                'body' => 'bar',
            ],
            [
                'title' => new ValidationRuleBuilder(
                    static function ($attribute, $value, $fail): void {
                        if ('foo' === $value) {
                            $fail('The '.$attribute.' is invalid.');
                        }
                    }
                ),
                'body' => new ValidationRuleBuilder(
                    static function ($attribute, $value, $fail): void {
                        if ('foo' === $value) {
                            $fail('The '.$attribute.' is invalid.');
                        }
                    }
                ),
            ],
        );

        static::assertTrue($validator->fails());

        static::assertCount(1, $validator->errors());

        static::assertTrue($validator->errors()->has('title'));
        static::assertFalse($validator->errors()->has('body'));
    }
}
