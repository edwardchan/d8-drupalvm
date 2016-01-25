var gulp = require('gulp');
var browserify = require('browserify');
var babelify = require('babelify');
var buffer = require('vinyl-buffer');
var source = require('vinyl-source-stream');
var uglify = require('gulp-uglify');
var watch = require('gulp-watch');

gulp.task('build', function () {
  browserify({
    entries: 'js/src/main.js',
    //extensions: ['.jsx'],		      
    debug: true
  })
  .transform(babelify)
  .bundle()
  .pipe(source('bundle.js'))
  .pipe(buffer())
  .pipe(uglify())
  .pipe(gulp.dest('js'));
});

gulp.task('default', ['build']);

gulp.task("watch", function() {
    watch("js/src/**/*.js", function() {
      gulp.start("default");
    });
});

