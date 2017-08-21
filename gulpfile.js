'use strict';

var gulp = require('gulp');
var plugins = require('gulp-load-plugins')();
var del = require('del');
var Q = require('q');

var config = {
    assetsDir: 'app/Resources/assets',
    bowerDir: 'vendor/bower_components',
    sassPattern: 'sass/**/*.scss',
    production: !!plugins.util.env.production,
    sourceMaps: !plugins.util.env.production,
    revManifestPath: 'app/Resources/assets/rev-manifest.json'
};

var app = {};

app.addStyle = function (paths, filename) {
    return gulp.src(paths)
        .pipe(plugins.plumber())
        .pipe(plugins.if(config.sourceMaps, plugins.sourcemaps.init()))
        .pipe(plugins.sass())
        .pipe(plugins.concat('css/'+filename))
        .pipe(plugins.if(config.production, plugins.minifyCss()))
        .pipe(plugins.rev())
        .pipe(plugins.if(config.sourceMaps, plugins.sourcemaps.write('.')))
        .pipe(gulp.dest('web'))
        .pipe(plugins.rev.manifest(config.revManifestPath, {
            merge: true
        }))
        .pipe(gulp.dest('.'));
};

app.addScript = function (paths, filename) {
    return gulp.src(paths)
        .pipe(plugins.plumber())
        .pipe(plugins.if(config.sourceMaps, plugins.sourcemaps.init()))
        .pipe(plugins.concat('js/'+filename))
        .pipe(plugins.if(config.production, plugins.uglify()))
        .pipe(plugins.rev())
        .pipe(plugins.if(config.sourceMaps, plugins.sourcemaps.write('.')))
        .pipe(gulp.dest('web'))
        .pipe(plugins.rev.manifest(config.revManifestPath, {
            merge: true
        }))
        .pipe(gulp.dest('.'));
};

app.copy = function (srcFiles, dstFiles) {
    gulp.src(srcFiles)
        .pipe(gulp.dest(dstFiles));
};

var Pipeline = function() {
    this.entries = [];
};

Pipeline.prototype.add = function() {
    this.entries.push(arguments);
};

Pipeline.prototype.run = function(callable) {
    var deferred = Q.defer();
    var i = 0;
    var entries = this.entries;
    var runNextEntry = function() {
        // see if we're all done looping
        if (typeof entries[i] === 'undefined') {
            deferred.resolve();
            return;
        }
        // pass app as this, though we should avoid using "this"
        // in those functions anyways
        callable.apply(app, entries[i]).on('end', function() {
            i++;
            runNextEntry();
        });
    };
    runNextEntry();
    return deferred.promise;
};

gulp.task('styles', function () {
    var pipeline = new Pipeline();

    pipeline.add([
        config.bowerDir + '/bootstrap/dist/css/bootstrap.css',
        config.bowerDir + '/font-awesome/css/font-awesome.css',
        config.assetsDir + '/sass/base.scss'
    ], 'main.css');

    pipeline.add([
        config.assetsDir + '/sass/home.scss'
    ], 'home.css');

    return pipeline.run(app.addStyle);
});

gulp.task('scripts', function () {
    var pipeline = new Pipeline();

    pipeline.add([
        config.bowerDir + '/jquery/dist/jquery.js',
        config.bowerDir + '/tether/dist/js/tether.js',
        config.bowerDir + '/bootstrap/dist/js/bootstrap.js',
        config.assetsDir + '/js/main.js'
    ], 'site.js');

    pipeline.add([
        config.bowerDir + '/underscore/underscore-min.js'
    ], 'underscore.js');

    return pipeline.run(app.addScript);
});

gulp.task('fonts', function () {
    app.copy(
        config.bowerDir + '/font-awesome/fonts/*',
        'web/fonts'
    );
});

gulp.task('images', function () {
    return app.copy(
        config.assetsDir + '/images/*',
        'web/images'
    );
});

gulp.task('clean', function () {
    del.sync(config.revManifestPath);
    del.sync('web/css/*');
    del.sync('web/js/*');
    del.sync('web/fonts/*');
    del.sync('web/images/*');
});

gulp.task('watch', function () {
    gulp.watch(config.assetsDir + '/' + config.sassPattern, ['styles']);
    gulp.watch(config.assetsDir + '/js/**/*.js', ['scripts']);
});

gulp.task('default', ['clean', 'styles', 'scripts', 'fonts', 'images', 'watch']);