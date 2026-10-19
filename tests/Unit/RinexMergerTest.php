<?php

use App\Services\RinexMerger;

it('merges one rinex type into a single header and updates the last observation', function () {
    $directory = sys_get_temp_dir().'/rinex-merge-'.uniqid();
    mkdir($directory);

    $first = <<<'RINEX'
     3.02           OBSERVATION DATA    M (MIXED)           RINEX VERSION / TYPE
  2026    10    15     0     0    0.0000000     GPS         TIME OF FIRST OBS
  2026    10    15     0    59   30.0000000     GPS         TIME OF LAST OBS
                                                            END OF HEADER
> EPOCH-A
SAT-A
RINEX;

    $second = <<<'RINEX'
     3.02           OBSERVATION DATA    M (MIXED)           RINEX VERSION / TYPE
  2026    10    15     1     0    0.0000000     GPS         TIME OF FIRST OBS
  2026    10    15     1    59   30.0000000     GPS         TIME OF LAST OBS
                                                            END OF HEADER
> EPOCH-B
SAT-B
RINEX;

    file_put_contents($directory.'/KOGI288A.26o', $first);
    file_put_contents($directory.'/KOGI288B.26o', $second);

    $merger = new RinexMerger;
    $output = $directory.'/KOGI2880.26o';
    $merger->merge([
        $directory.'/KOGI288A.26o',
        $directory.'/KOGI288B.26o',
    ], $output);

    $merged = file_get_contents($output);

    expect($merger->dailyName($directory.'/KOGI288A.26o'))->toBe('KOGI2880.26o')
        ->and(substr_count($merged, 'END OF HEADER'))->toBe(1)
        ->and(substr_count($merged, 'COMMENT'))->toBe(1)
        ->and($merged)->toContain('> EPOCH-A')
        ->and($merged)->toContain('SAT-A')
        ->and($merged)->toContain('> EPOCH-B')
        ->and($merged)->toContain('SAT-B')
        ->and($merged)->toContain('1    59   30.0000000')
        ->and($merged)->not->toContain('0    59   30.0000000');
});

it('thins observation epochs to the requested interval and leaves other extensions alone', function () {
    $merger = new RinexMerger;

    expect($merger->resolvesInterval('26o', 30, ['o']))->toBe(30)
        ->and($merger->resolvesInterval('26n', 30, ['o']))->toBeNull()
        ->and($merger->resolvesInterval('26g', 30, ['o']))->toBeNull()
        ->and($merger->resolvesInterval('26o', 0, ['o']))->toBeNull()
        ->and($merger->resolvesInterval('26o', 30, ['26o']))->toBe(30);

    $directory = sys_get_temp_dir().'/rinex-thin-'.uniqid();
    mkdir($directory);

    $header = <<<'RINEX'
     3.02           OBSERVATION DATA    M (MIXED)           RINEX VERSION / TYPE
     1.000                                                  INTERVAL
  2026    10    15     0     0   18.0000000     GPS         TIME OF FIRST OBS
  2026    10    15     0     1   16.0000000     GPS         TIME OF LAST OBS
                                                            END OF HEADER
RINEX;

    $first = $header."\n"
        ."> 2026 10 15 00 00 18.0000000  0 1\n"
        ."G01 dropped-18\n"
        ."> 2026 10 15 00 00 30.0000000  0 1\n"
        ."G01 kept-30\n";

    $second = $header."\n"
        ."> 2026 10 15 00 01 00.0000000  0 1\n"
        ."G01 kept-00\n"
        ."> 2026 10 15 00 01 16.0000000  0 1\n"
        ."G01 dropped-16\n";

    file_put_contents($directory.'/KOGI288A.26o', $first);
    file_put_contents($directory.'/KOGI288B.26o', $second);

    $output = $directory.'/KOGI2880.26o';
    $merger->merge([
        $directory.'/KOGI288A.26o',
        $directory.'/KOGI288B.26o',
    ], $output, 30);

    $merged = file_get_contents($output);

    expect(substr_count($merged, 'END OF HEADER'))->toBe(1)
        ->and($merged)->toMatch('/30\.000\s+INTERVAL/')
        ->and($merged)->toContain('> 2026 10 15 00 00 30.0000000  0 1')
        ->and($merged)->toContain('G01 kept-30')
        ->and($merged)->toContain('> 2026 10 15 00 01 00.0000000  0 1')
        ->and($merged)->toContain('G01 kept-00')
        ->and($merged)->not->toContain('dropped-18')
        ->and($merged)->not->toContain('dropped-16')
        ->and($merged)->not->toContain('18.0000000')
        ->and($merged)->not->toContain('16.0000000')
        ->and($merged)->toContain('30.0000000     GPS         TIME OF FIRST OBS');
});
