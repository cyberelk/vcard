const { src, dest } = require("gulp");
const concat = require("gulp-concat");

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