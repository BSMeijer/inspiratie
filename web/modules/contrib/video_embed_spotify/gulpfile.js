/**
 * @file
 * Video Embed Spotify module Gulpfile.
 */

'use strict';
var gulp = require('gulp');
var phpcs = require('gulp-phpcs');

gulp.task('default', function () {
  return gulp.src(['*','**/*','!node_modules/**/*'])
  .pipe(phpcs({
    standard: 'Drupal,DrupalPractice',
    warningSeverity: 0
  }))
  .pipe(phpcs.reporter('log'));
});
