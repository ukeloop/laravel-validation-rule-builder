<?php

namespace Ukeloop\ValidationRuleBuilder;

abstract class RuleSet
{
    abstract public static function rules(): ValidationRuleBuilder;
}
