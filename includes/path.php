<?php

if (!function_exists('normalize_path')) :
function normalize_path ($path) {
  if (!strlen($path)) {
    return ".";
  }

  $isAbsolute  = $path[0];
  $trailingSlash = $path[strlen($path) - 1];

  $upsAtStart = explode("../", $path);
  $prefix = '';
  $sufix = '';
  foreach ($upsAtStart as $entry) {
    if (empty($entry) && empty($sufix)) {
      $prefix .= '../';
    } else {
      $sufix .= $entry;
    }
  }

  $up   = 0;
  $peices = array_values(array_filter(explode("/", $sufix), function($n) {
    return !!$n;
  }));
  for ($i = count($peices) - 1; $i >= 0; $i--) {
    $last = $peices[$i];
    if ($last == ".") {
      array_splice($peices, $i, 1);
    } else if ($last == "..") {
      array_splice($peices, $i, 1);
      $up++;
    } else if ($up) {
      array_splice($peices, $i, 1);
      $up--;
    }
  }

  $path = $prefix . implode("/", $peices);

  if (!$path && !$isAbsolute) {
    $path = ".";
  }

  if ($path && $trailingSlash == "/") {
    $path .= "/";
  }

  return ($isAbsolute == "/" ? "/" : "") . $path;
}
endif;
