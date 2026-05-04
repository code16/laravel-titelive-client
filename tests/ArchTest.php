<?php

it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'die', 'vardump'])
    ->each->not->toBeUsed();
