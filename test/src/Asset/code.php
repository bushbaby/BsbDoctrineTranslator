<?php

translate('foo');

$this->translate('foo');

// Collected but not managed as $foo will be an unknown Expr_Variable
$this->translate($foo);

// Collected but not managed as $foo->bar will be unknown Expr_PropertyFetch
$this->translate($foo->bar);

// Collected but not managed as $foo->bar() will be unknown Expr_PropertyFetch
$this->translate($foo->bar());
