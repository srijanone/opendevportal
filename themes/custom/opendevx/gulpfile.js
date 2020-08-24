var gulp = require('gulp');
var sass = require('gulp-sass');
var sassLint = require('gulp-sass-lint');

// Compile Scss
gulp.task('scss', () =>
  gulp.src('scss/style.scss')
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(gulp.dest('css'))
);

// Apply sass linting
gulp.task('sass-lint', () =>
  gulp.src('scss/**/*.scss')
   .pipe(sassLint({
     configFile: '.sasslintrc'
   }))
   .pipe(sassLint.format())
   .pipe(sassLint.failOnError())
);

// Watch The Multiple Tasks
gulp.task('watch', () =>
  gulp.watch('scss/**/*.scss', gulp.series('scss'))
);

// Default task
gulp.task('default', gulp.parallel('scss', 'sass-lint', 'watch'));