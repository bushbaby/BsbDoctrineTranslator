<?php

namespace BsbDoctrineTranslator\Model;

class FilterConstant {
    const NONE = null;

    const TYPE_NEW = 1;
    const TYPE_OBSOLETE = 2;
    const TYPE_SOURCE = 4;
    const TYPE_NONE = 7;

    // filters for dynamic definitions in source //
    const VARIABLE_NONE = 0;
    const VARIABLE_KEY = 1;
    const VARIABLE_LOCALE = 2;
    const VARIABLE_DOMAIN = 4;
    const VARIABLE_ANY = 7;
}