'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const compass = require('gulp-compass');
const clean = require('gulp-clean');

// gulp.task('compass', function() {
//   gulp.src('./sass/*.scss')
//     .pipe(compass({
//       project: __dirname,
//       css: 'css',
//       sass: 'sass'
//     }))
//     .pipe(gulp.dest('./css'));
// });

// const sassProd = () => {
//   gulp.src('./sass/*.scss')
//     .pipe(sass().on('error', sass.logError))
//     .pipe(autoprefixer({
//        browsers: ['last 2 version']
//     }))
//     .pipe(gulp.dest('./css'));
// };

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
    // .pipe(sass().on('error', sass.logError))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./css'));
};

// gulp.task('sass:watch', function () {
//   gulp.watch('./sass/**/*.scss', ['sass:dev']);
// });

// gulp.task('default', gulp.series('sass:dev', 'sass:watch'));
exports.default = gulp.series(deleteCssFiles, compileScss);
