/* Needed gulp config */

import gulp from 'gulp';
import sass from 'gulp-sass';
import uglify from 'gulp-uglify';
import rename from 'gulp-rename';
// import notify from 'gulp-notify';
import minifycss from 'gulp-minify-css';
import concat from 'gulp-concat';
import plumber from 'gulp-plumber';
import sourcemaps from 'gulp-sourcemaps';
import autoprefixer from 'gulp-autoprefixer';
import { reload } from 'browser-sync';

/* Setup scss path */
const paths = {
  scss: './sass/*.scss',
};

/* Scripts task */
export const scripts = () => {
  return gulp
    .src([
      /* Add your JS files here, they will be combined in this order */
      // 'js/vendor/jquery.min.js',
      // 'js/vendor/jquery.easing.1.3.js',
      // 'js/vendor/jquery.stellar.min.js',
      // 'js/vendor/owl.carousel.min.js',
      // 'js/vendor/bootstrap.min.js',
      'js/vendor/jquery.waypoints.min.js',
    ])
    .pipe(concat('scripts.js'))
    .pipe(gulp.dest('js'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(gulp.dest('js'));
};

export const minifyCustom = () => {
  return gulp
    .src([
      /* Add your JS files here, they will be combined in this order */
      'js/custom.js',
    ])
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(gulp.dest('js'));
};

/* Sass task */
export const sassTask = () => {
  return gulp
    .src('scss/style.scss')
    .pipe(plumber())
    .pipe(
      sass({
        errLogToConsole: true,

        //outputStyle: 'compressed',
        // outputStyle: 'compact',
        // outputStyle: 'nested',
        outputStyle: 'expanded',
        precision: 10,
      })
    )

    .pipe(sourcemaps.init())
    .pipe(
      autoprefixer({
        browsers: ['last 2 versions'],
        cascade: false,
      })
    )
    .pipe(gulp.dest('css'))

    .pipe(rename({ suffix: '.min' }))
    .pipe(minifycss())
    .pipe(gulp.dest('css'))
    /* Reload the browser CSS after every change */
    .pipe(bsReload({ stream: true }));
};

export const mergeStyles = () => {
  return gulp
    .src([
      // 'css/vendor/bootstrap.min.css',
      'css/vendor/animate.css',
      'css/vendor/icomoon.css',
      'css/vendor/owl.carousel.min.css',
      'css/vendor/owl.theme.default.min.css',
      // 'fonts/icomoon/style.css',
      'css/template-style.css',
    ])
    // .pipe(sourcemaps.init())
    // .pipe(autoprefixer({
    //     cascade: false
    // }))
    .pipe(concat('styles-merged.css'))
    // .pipe(gulp.dest('css'))
    .pipe(rename({suffix: '.min'}))
    .pipe(minifycss())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('css'))
    .pipe(reload({ stream: true }));
};

/* Reload task */
export const bsReload = () => {
  reload()
};

/* Prepare Browser-sync for localhost */
export const browserSync = async (done) => {
  const bs = await import('browser-sync');
  bs.init(['css/*.css', 'js/*.js'], {
    /*
    I like to use a vhost, WAMP guide: https://www.kristengrote.com/blog/articles/how-to-set-up-virtual-hosts-using-wamp, XAMP guide: http://sawmac.com/xampp/virtualhosts/
    */
    proxy: 'localhost/new-kingsoft',
    /* For a static server you would use this: */
    /*
    server: {
        baseDir: './'
    }
    */
  });
  done()

};

/* Watch scss, js and html files, doing different things with each. */
export default gulp.series(
  gulp.parallel(scripts, browserSync),
  function () {
    /* Watch scss, run the sass task on change. */
    // gulp.watch(['scss/*.scss', 'scss/**/*.scss'], sass);
    /* Watch app.js file, run the scripts task on change. */
    gulp.watch(['js/custom.js'], minifyCustom);
    // Watch css files and run mergeStyles task on change
    gulp.watch(['css/*'], mergeStyles);
    // watch js 
    gulp.watch(['js/custom.js'], minifyCustom);
    /* Watch .html files, run the bs-reload task on change. */
    gulp.watch(['*.html'], bsReload);
  }
);
