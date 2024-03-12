const { src, dest, watch, parallel } = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const autoprefixer = require("autoprefixer");
const postcss = require("gulp-postcss");
const sourcemaps = require("gulp-sourcemaps");
const cssnano = require("cssnano");
const terser = require("gulp-terser-js");
const notify = require("gulp-notify");

const paths = {
  scss: "src/scss/**/*.scss",
  js: "src/js/**/*.js",
  imagenes: "src/img/**/*",
};

async function imagenes() {
  const webp = await import("gulp-webp");
  return src(paths.imagenes)
    .pipe(webp.default())
    .pipe(dest("build/img"))
    .pipe(notify({ message: "Imagen Completada" }));
}

async function versionWebp() {
  const webp = await import("gulp-webp");
  return src(paths.imagenes)
    .pipe(webp.default())
    .pipe(dest("build/img"))
    .pipe(notify({ message: "Imagen Completada" }));
}

function css() {
  return src(paths.scss)
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(sourcemaps.write("."))
    .pipe(dest("build/css"));
}

function javascript() {
  return src(paths.js)
    .pipe(terser())
    .pipe(sourcemaps.write("."))
    .pipe(dest("build/js"));
}

function watchArchivos() {
  watch(paths.scss, css);
  watch(paths.js, javascript);
  watch(paths.imagenes, imagenes);
  watch(paths.imagenes, versionWebp);
}

exports.css = css;
exports.watchArchivos = watchArchivos;
exports.default = parallel(
  css,
  javascript,
  imagenes,
  versionWebp,
  watchArchivos
);