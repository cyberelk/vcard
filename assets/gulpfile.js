const { src, dest } = require("gulp");
const concat = require("gulp-concat");
const sass = require('gulp-sass')(require('sass'));
const minify = require('gulp-uglify');

function concatFiles(cb) {
  src([
    "./vendor/jquery/jquery.min.js",
    "./vendor/bootstrap/js/bootstrap.bundle.js", 
    "./vendor/jquery.easing/jquery.easing.js",
    "./vendor/jquery.appear/jquery.appear.js",
    "./vendor/imagesloaded/imagesloaded.pkgd.min.js",
    "./vendor/jquery.countTo/jquery.countTo.js",
    "./vendor/parallaxie/parallaxie.js",
    "./vendor/typed/typed.js",
    "./vendor/owl.carousel/owl.carousel.js",
    "./vendor/isotope/isotope.pkgd.min.js",
    "./vendor/magnific-popup/jquery.magnific-popup.js",
    "./javascript/theme.js",
  ])
    .pipe(concat("scripts.js"))
    .pipe(dest("../public_html/js"));

  cb();
}
exports.concat = concatFiles;

function minifyJsFile(cb){
  src("../public_html/js/scripts.js")
    .pipe(minify())
    .pipe(dest("../public_html/js"));

  cb();
}
exports.minify = minifyJsFile;

function copy(cb) {
  src([
    "./googlefonts/*.ttf",
    "./googlefonts/*.woff2",
    "./vendor/font-awesome/webfonts/*.ttf",
    "./vendor/font-awesome/webfonts/*.woff2",
  ], {encoding: false})
      .pipe(dest('../public_html/fonts'));
  cb();
}
exports.copy = copy;

function compileSass() {
  return src('./sass/stylesheet.scss', { sourcemaps: true })
    .pipe(sass().on('error', sass.logError))
    .pipe(dest('../public_html/css/', { sourcemaps: '.' }));
};
exports.sass = compileSass;

