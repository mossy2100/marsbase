'use strict';

const gulp = require('gulp');
const sourcemaps = require('gulp-sourcemaps');
const compass = require('gulp-compass');
const clean = require('gulp-clean');

/**
 * Delete the CSS files.
 */
const deleteCssFiles = () => {
  return gulp.src('./css/*.css', { read: false })
    .pipe(clean());
};

/**
 * Compile the SCSS files.
 */
const compileScss = () => {
  return gulp.src('./sass/*.scss')
    .pipe(sourcemaps.init())
    .pipe(compass({
      project: __dirname,
      css: 'css',
      sass: 'sass',
      debug: true,
      environment: 'development'
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./css'));
};

const watch = () => {
   gulp.watch('./sass/**/*.scss', ['compileScss']);
};

exports.default = gulp.series(deleteCssFiles, compileScss);
exports.watch = watch;
