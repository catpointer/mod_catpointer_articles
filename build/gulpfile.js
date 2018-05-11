var gulp = require('gulp');

var extension = require('./package.json');
var config    = require('./gulp-config.json');

var requireDir = require('require-dir');
var del         = require('del');
var zip        = require('gulp-zip');
var fs         = require('fs');
var xml2js     = require('xml2js');
var parser     = new xml2js.Parser();

var jgulp = requireDir('./node_modules/joomla-gulp', {recurse: true});
var dir = requireDir('./joomla-gulp-extensions', {recurse: true});

var rootPath = '../extensions/modules/site/mod_catpointer_articles';

gulp.task('clean-deps-tmp', function(cb) {
	return del(rootPath + '/dependencies/install_*', {force : true});
});

// Override of the release script
gulp.task('release', ['clean-deps-tmp'], function (cb) {
	fs.readFile(rootPath + '/mod_catpointer_articles.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			var version = result.extension.version[0];

			var fileName = extension.name + '-v' + version + '.zip';

			return gulp.src([
					rootPath + '/**/*',
					'!' + rootPath + '/libraries/**/docs/**/*',
					'!' + rootPath + '/libraries/**/docs',
					'!' + rootPath + '/libraries/**/examples/**/*',
					'!' + rootPath + '/libraries/**/examples',
					'!' + rootPath + '/libraries/**/doc/**/*',
					'!' + rootPath + '/libraries/**/doc',
					'!' + rootPath + '/libraries/**/composer.*',
					'!' + rootPath + '/libraries/**/build.php',
					'!' + rootPath + '/libraries/**/phpunit.*',
				],{ base: rootPath })
				.pipe(zip(fileName))
				.pipe(gulp.dest('releases'))
				.on('end', cb);
		});
	});
});
