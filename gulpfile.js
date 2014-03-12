var gulp       = require('gulp');
var gutil      = require('gulp-util');
var notify     = require('gulp-notify');
var autoprefix = require('gulp-autoprefixer');
var minifyCSS  = require('gulp-minify-css');
var uglify     = require('gulp-uglify');
var less       = require('gulp-less');
var rename     = require("gulp-rename");
var concat     = require('gulp-concat');

// Where do you store your JS files?
var jsDir       = 'vendor/syntax/core/public/js';
var targetJSDir = 'public/js';

// Extra JS
var jquery         = 'public/js/jquery-1.10.2.min.js';
var bootstrap      = 'public/vendor/bootstrap3/dist/js/bootstrap.min.js';
var bootbox        = 'public/vendor/bootbox/bootbox.js';
var messenger      = 'public/vendor/messenger/build/js/messenger.min.js';
var messengerTheme = 'public/vendor/messenger/build/js/messenger-theme-future.js';

// Where do you store your css files?
var lessDir      = 'vendor/syntax/core/assets/less';
var targetCSSDir = 'public/css';

gulp.task('js', function() {
	return gulp.src([jquery, jsDir + '/**/*.js', bootstrap, bootbox, messenger, messengerTheme])
		.pipe(uglify())
		.pipe(concat('all.js', {"newLine": "\r\n"}))
		.pipe(gulp.dest(targetJSDir))
		.pipe(notify('JS minified'))
});

gulp.task('css', function() {
	return gulp.src(lessDir + '/master.less')
		.pipe(less())
		.pipe(minifyCSS())
		.pipe(rename('master.css'))
		.pipe(gulp.dest(targetCSSDir))
		.pipe(notify('Master CSS minified'))
});

gulp.task('userCss', function() {
	return gulp.src(targetCSSDir + '/users/**/*.less')
		.pipe(less())
		.pipe(minifyCSS())
		.pipe(gulp.dest(targetCSSDir + '/users'))
		.pipe(notify('User CSS minified'))
});

gulp.task('watch', function () {
	gulp.watch(jsDir + '/**/*.js', ['js']);
	gulp.watch(targetCSSDir + '/colors.less', ['css']);
	gulp.watch(lessDir + '/master.less', ['css']);
	gulp.watch(lessDir + '/imports.less', ['css', 'userCss']);
	gulp.watch(lessDir + '/master_mixins.less', ['css', 'userCss']);
});

gulp.task('install', ['js', 'css', 'userCss']);

gulp.task('default', ['js', 'css', 'userCss', 'watch']);