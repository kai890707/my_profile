<?php

declare(strict_types=1);

use App\Services\SensitiveWordFilter;

beforeEach(function (): void {
    $this->filter = new SensitiveWordFilter();
});

test('filter detects sensitive words', function (): void {
    expect($this->filter->check('這是垃圾評論'))->toBeTrue();
    expect($this->filter->check('請加line聯絡我'))->toBeTrue();
    expect($this->filter->check('這是正常的評論'))->toBeFalse();
});

test('filter finds all sensitive words in text', function (): void {
    $text = '這是垃圾評論，請加line';
    $found = $this->filter->find($text);

    expect($found)->toContain('垃圾');
    expect($found)->toContain('加line');
    expect($found)->toHaveCount(2);
});

test('filter masks sensitive words', function (): void {
    $masked = $this->filter->mask('這是垃圾評論');

    expect($masked)->toContain('**');
    expect($masked)->not->toContain('垃圾');
});

test('filter is case insensitive', function (): void {
    expect($this->filter->check('SPAM評論'))->toBeTrue();
    expect($this->filter->check('spam評論'))->toBeTrue();
});
