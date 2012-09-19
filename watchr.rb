require 'watchr'
require 'zlib';
script = Watchr::Script.new

script.watch('less/.*\.less$') do |f| 
  print 'Recompile less files...';
  time = Time.now
  system("lessc less/project.less > css/project.css");
  print " done in #{((Time.now - time) * 1000).round}ms...";
  time = Time.now
  print " and compress them..."
  system("cssmin css/project.css --compress > css/project.min.css");
  puts " done in #{((Time.now - time) * 1000).round}ms...";
end
script.watch('js/(lib/)?[a-z]+\.js') do |f|
  minimizeJSFile f[0]
  print " concenate... "
  concentateJSFiles()
  puts ' done...'
end

def minimizeJSFile(filename)
  print 'Minimize ' + filename + '...';
  time = Time.now
  system('uglifyjs -o js/min/' + File.basename(filename).gsub(".js", ".min.js") + ' ' +  filename );
  print " done in #{((Time.now - time) * 1000).round}ms...";
end

def concentateJSFiles
    not_concenate = ["js/libs/jquery-1.7.2.js", "js/libs/modernizr-2.5.3.min.js", "js/libs/jquery.wysiwyg.js"]
    not_concenate_min = not_concenate.map {|x| "js/min/" + File.basename(x).gsub(".js", ".min.js")}
    files = Dir.glob("js/min/*.js") - ["js/min/scripts.min.js"] - not_concenate_min
    File.open( "js/min/scripts.min.js", "w" ) do |f_out|
        files.each do |f_name|
            f_out.puts("\n//@ sourceMappingURL=#{f_name}.map")
            File.open(f_name) do |f_in|
                f_in.each {|f_str| f_out.puts(f_str) }
            end
        end
    end
    bool = false
    (Dir.glob("js/libs/*.js") + Dir.glob("js/*.js") - not_concenate).each do |f|
        basename = File.basename(f)
	if !files.include? 'js/min/' + File.basename(f).gsub(".js", ".min.js")
             minimizeJSFile f
	     puts
	     bool = true
	end
    end
    concentateJSFiles() if bool
end
concentateJSFiles()
handler = Watchr.handler.new;
controller = Watchr::Controller.new(script,handler);
controller.run # does block!