<?php
/**
 * KATEGORI DEFINITIONS
 * Auto-generated category definitions
 */

define('KATEGORI_DEFINITIONS', array (
  'teknologi' => 
  array (
    'name' => 'Teknologi',
    'color' => '#667eea',
    'icon' => '💻',
  ),
  'pendidikan' => 
  array (
    'name' => 'Pendidikan',
    'color' => '#2ecc71',
    'icon' => '📚',
  ),
  'bisnis' => 
  array (
    'name' => 'Bisnis',
    'color' => '#e74c3c',
    'icon' => '💼',
  ),
  'kesehatan' => 
  array (
    'name' => 'Kesehatan',
    'color' => '#f39c12',
    'icon' => '🏥',
  ),
  'sains' => 
  array (
    'name' => 'Sains',
    'color' => '#9b59b6',
    'icon' => '🔬',
  ),
  'lifestyle' => 
  array (
    'name' => 'Lifestyle',
    'color' => '#1abc9c',
    'icon' => '🌟',
  ),
  'olahraga' => 
  array (
    'name' => 'Olahraga',
    'color' => '#e67e22',
    'icon' => '⚽',
  ),
  'hiburan' => 
  array (
    'name' => 'Hiburan',
    'color' => '#ff6b9d',
    'icon' => '🎬',
  ),
  'umum' => 
  array (
    'name' => 'Umum',
    'color' => '#95a5a6',
    'icon' => '📄',
  ),
));

function getKategoriInfo($category_enum) {
    $definitions = KATEGORI_DEFINITIONS;
    return $definitions[$category_enum] ?? $definitions['umum'];
}

function getAllKategori() {
    return KATEGORI_DEFINITIONS;
}
