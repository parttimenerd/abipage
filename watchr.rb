require 'watchr'
require 'colored'
require 'win32console'
require 'open3'
script = Watchr::Script.new

script.watch('less/.*\.less$') do |f| 
  print 'Recompile less files...';
  time = Time.now
  system("lessc less/project.less > css/project.css");
  print " done in #{((Time.now - time) * 1000).round}ms...";
  time = Time.now
  print " and compress them..."
  runCommand("cssmin css/project.css --compress > css/project.min.css");
  puts " done in #{((Time.now - time) * 1000).round}ms...";
end
script.watch('js/(lib/)?[a-z.]+\.js') do |f|
  begin
    minimizeJSFile f[0]
    print " concenate... "
    concentateJSFiles()
    puts ' done...'
  rescue Exception => ex
      puts ex.to_s.red
  end
end
script.watch('coffee/(lib/)?[a-z]+\.coffee') do |f|
    if (compileCoffeeScriptFile f[0])
	    puts
	    minimizeJSFile f[0].gsub("coffee", "js")
	    print " concenate... "
	    concentateJSFiles()
	    puts ' done...'
    end
end
def minimizeJSFile(filename)
  print 'Minimize ' + filename + '...';
  time = Time.now
  name_app = filename =~ /(lib\/)/ ? "lib/" : ""
  system('uglifyjs -o js/min/' + name_app + File.basename(filename).gsub(".js", ".min.js") + ' ' +  filename );
  print " done in #{((Time.now - time) * 1000).round}ms...";
end
def compileCoffeeScriptFile(filename)
  print 'Compile ' + filename + '...';
  time = Time.now
  if runCommand('coffee --compile --output js/ coffee/') # + filename + ' -o ' + filename.gsub("coffee", "js")
    print " done in #{((Time.now - time) * 1000).round}ms...".green
    return true
  end
  return false
end
def runCommand(cmd)
  res = %x[#{cmd} 2>&1].inspect
  if (res.length > 10 && res !~ /(path.exists is now called)/)
    puts 
    puts res.split('\n')[0][1..-1].red
    return false
  end
  return true
end
def concentateJSFiles
    not_concenate = ["js/libs/jquery-1.7.2.js", "js/libs/modernizr-2.5.3.min.js"]
    not_concenate_min = not_concenate.map {|x| "js/min/" + File.basename(x).gsub(".js", ".min.js")}
    files = Dir.glob("js/min/*.js") + Dir.glob("js/min/lib/*.js") - ["js/min/scripts.min.js"] - not_concenate_min
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
	name_app = f =~ /(lib\/)/ ? "lib/" : ""
	if !files.include? 'js/min/' + name_app + File.basename(f).gsub(".js", ".min.js")
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