module.exports = function (grunt) {

  /** task to run */
  var tasks = ['clean', 'copy', 'uglify', 'compass', 'curl', 'writeHTML'];

  var configuration = require('./Gruntconfigurations');

  var taskConfiguration = {
    pkg: grunt.file.readJSON('package.json'),
	  clean: configuration.clean(),
	  copy: configuration.copy(),
	  uglify: configuration.uglify(),
	  watch: configuration.watch(),
	  compass : configuration.compass()
  };

  if (grunt.file.isFile(__dirname + '/source/json/curl.json')) {
	  taskConfiguration.curl = configuration.curl();
  }

  /** project configuration */
  grunt.initConfig(taskConfiguration);

  /** load modules and tasks */
  grunt.loadNpmTasks('grunt-curl');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  require('./lib/grunt-contrib-writeHTML/tasks/writeHTML.js')(grunt);

  /** register the task */
  grunt.registerTask('default', tasks);

};
