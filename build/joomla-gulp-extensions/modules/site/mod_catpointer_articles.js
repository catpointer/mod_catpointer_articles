var gulp      = require('gulp');
var config    = require('../../../gulp-config.json');
var extension = require('../../../package.json');

// Dependencies
var beep        = require('beepbeep');
var browserSync = require('browser-sync');
var cleanCSS    = require('gulp-clean-css');
var concat      = require('gulp-concat');
var del         = require('del');
var gutil       = require('gulp-util');
var less        = require('gulp-less');
var path        = require('path');
var plumber     = require('gulp-plumber');
var rename      = require('gulp-rename');
var uglify      = require('gulp-uglify');

var modName   = "articles";
var modFolder = "mod_catpointer_" + modName;
var modBase   = "site";

var baseTask  = 'modules.frontend.' + modName;
var extPath   = '../extensions/modules/' + modBase + '/' + modFolder;
var mediaPath = extPath + '/media/' + modFolder;
var assetsPath = './media/modules/' + modBase + '/' + modFolder;
var nodeModulesPath = './node_modules';

var wwwPath = config.wwwDir + '/modules/' + modFolder
var wwwMediaPath = config.wwwDir + '/media/' + modFolder;

var onError = function (err) {
    beep([0, 0, 0]);
    gutil.log(gutil.colors.green(err));
};

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':module',
		'clean:' + baseTask + ':media'
	],
	function() {
	});

// Clean: Module
gulp.task('clean:' + baseTask + ':module', function() {
	return del(wwwPath, {force: true});
});

// Clean: Media
gulp.task('clean:' + baseTask + ':media', function() {
	return del(wwwMediaPath, {force: true});
});

// Copy: Module
gulp.task('copy:' + baseTask,
	[
		'clean:' + baseTask,
		'copy:' + baseTask + ':module',
		'copy:' + baseTask + ':media'
	],
	function() {
	});

// Copy: Module
gulp.task('copy:' + baseTask + ':module', ['clean:' + baseTask + ':module'], function() {
	return gulp.src([
			extPath + '/**',
			'!' + extPath + '/media',
			'!' + extPath + '/media/**'
		])
		.pipe(gulp.dest(wwwPath));
});

// Copy: Media
gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
	return gulp.src([
			mediaPath + '/**'
		])
		.pipe(gulp.dest(wwwMediaPath));
});

function compileScripts(src, ouputFileName, destinationFolder) {
	return gulp.src(src)
		.pipe(plumber({ errorHandler: onError }))
		.pipe(concat(ouputFileName))
		.pipe(gulp.dest(mediaPath + '/' + destinationFolder))
		.pipe(gulp.dest(wwwMediaPath + '/' + destinationFolder))
		.pipe(uglify())
		.pipe(rename(function (path) {
			path.basename += '.min';
		}))
		.pipe(gulp.dest(mediaPath + '/' + destinationFolder))
		.pipe(gulp.dest(wwwMediaPath + '/' + destinationFolder))
		.pipe(browserSync.reload({stream:true}));
}

// Scripts
gulp.task('scripts:' + baseTask, function () {

	return compileScripts(
		[
			assetsPath + '/js/module.js'
		],
		'module.js',
		'js'
	);
});

function compileLessFile(src, destinationFolder, options)
{
	return gulp.src(src)
		.pipe(plumber({ errorHandler: onError }))
		.pipe(less({paths: [assetsPath + '/less']}))
		.pipe(gulp.dest(mediaPath + '/' + destinationFolder))
		.pipe(gulp.dest(wwwMediaPath + '/' + destinationFolder))
		.pipe(browserSync.reload({stream:true}))
		.pipe(cleanCSS())
		.pipe(rename(function (path) {
			path.basename += '.min';
		}))
		.pipe(gulp.dest(mediaPath + '/' + destinationFolder))
		.pipe(gulp.dest(wwwMediaPath + '/' + destinationFolder))
		.pipe(browserSync.reload({stream:true}));
}

gulp.task('less:' + baseTask, function () {
	return compileLessFile(
		assetsPath + '/less/module.less',
		'css'
	);
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':module',
		'watch:' + baseTask + ':scripts',
		'watch:' + baseTask + ':less'
	],
	function() {
	});

// Watch: Module
gulp.task('watch:' + baseTask + ':module', function() {
	gulp.watch([
			extPath + '/**/*',
			'!' + extPath + '/media',
			'!' + extPath + '/media/**/*'
		],
		['copy:' + baseTask + ':module', browserSync.reload]);
});

// Watch: Scripts
gulp.task('watch:' + baseTask + ':scripts', function() {
	gulp.watch([
			assetsPath + '/js/**/*.js'
		],
		['scripts:' + baseTask]);
});

// Watch: LESS
gulp.task('watch:' + baseTask + ':less', function() {
	gulp.watch([
			assetsPath + '/less/**/*.less'
		],
		['less:' + baseTask, browserSync.reload]);
});

