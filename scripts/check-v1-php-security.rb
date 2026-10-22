#!/usr/bin/env ruby
# Offline checks only: no Docker daemon, image build, package install, or network.
require 'json'
require 'yaml'
require 'open3'
require 'tmpdir'
require 'fileutils'

ROOT = File.expand_path('..', __dir__)
LEGACY_VERSIONS = {
  '5.6.41' => ['php5', '5.6.40'],
  '7.0.34' => ['php7', '7.0.33'],
  '7.1.34' => ['php7', '7.1.33'],
  '7.2.35' => ['php7', '7.2.34'],
  '7.3.34' => ['php7', '7.3.33']
}.freeze
VERSIONS = LEGACY_VERSIONS.merge({
  '7.4.34' => ['php7', '7.4.33'],
  '8.0.31' => ['php8', '8.0.30'],
  '8.1.35' => ['php8', '8.1.34'],
  '8.2.35' => ['php8', '8.2.35'],
  '8.3.36' => ['php8', '8.3.36'],
  '8.4.27' => ['php8', '8.4.27'],
  '8.5.12' => ['php8', '8.5.12']
}).freeze

def check(value, message)
  raise message unless value
end

def yaml(path)
  YAML.safe_load(File.read(path))
end

templates = nil
legacy_templates = nil
VERSIONS.each do |tag, (app, internal)|
  package = File.join(ROOT, 'apps', app, tag)
  build = File.join(package, 'build')
  env = File.readlines(File.join(build, '.env')).map(&:strip).reject { |l| l.empty? || l.start_with?('#') }.to_h { |l| l.split('=', 2) }
  check(env['PHP_VERSION'] == tag, "#{tag}: image tag")
  check(env['IMAGE_NAME'] == "1panel-php:#{tag}", "#{tag}: output image")
  compose = yaml(File.join(build, 'docker-compose.yml'))
  service = compose.fetch('services').fetch('1panel-php')
  check(service.fetch('build').fetch('context') == './php', "#{tag}: v1 build context")
  args = service.fetch('build').fetch('args')
  check(args.fetch('PHP_IMAGE').gsub('${PHP_VERSION}', env['PHP_VERSION']) == "1panel/php:#{tag}-fpm", "#{tag}: image reference")
  check(args.fetch('PHP_BASE_VERSION') == internal, "#{tag}: real PHP baseline")
  compose.to_json.scan(/\$\{([^}]+)\}/).flatten.each { |key| check(env.key?(key), "#{tag}: missing .env #{key}") }
  check(service.fetch('volumes').include?('${PHP_FPM_CONF_FILE}:/usr/local/etc/php-fpm.d/www.conf'), "#{tag}: build FPM mount")
  check(service.fetch('volumes').include?('${DATA_DIR}/composer:/tmp/composer'), "#{tag}: composer mount")
  config = JSON.parse(File.read(File.join(build, 'config.json')))
  field = config.fetch('formFields').find { |f| f['envKey'] == 'PHP_EXTENSIONS' }
  choices = field.fetch('values').map { |v| v.fetch('value') }
  check(choices.uniq == choices && (field.fetch('default') - choices).empty?, "#{tag}: extension form")
  outer = yaml(File.join(package, 'docker-compose.yml')).fetch('services').fetch('php')
  check(outer.fetch('image') == '${IMAGE_NAME}', "#{tag}: v1 runtime image")
  check(outer.fetch('networks') == ['1panel-network'], "#{tag}: runtime network")
  check(outer.fetch('ports') == ['127.0.0.1:${PANEL_APP_PORT_HTTP}:9000'], "#{tag}: host loopback binding")
  check(outer.fetch('volumes').include?('./extensions:/php/extensions'), "#{tag}: v1 extensions mount")
  check(outer.fetch('volumes').include?('./conf/php-fpm.conf:/usr/local/etc/php-fpm.d/www.conf'), "#{tag}: runtime FPM mount")
  %w[conf/php.ini conf/php-fpm.conf].each do |path|
    check(File.read(File.join(package, path)) == File.read(File.join(build, 'php', File.basename(path))), "#{tag}: build/runtime config drift")
  end
  pool = File.read(File.join(package, 'conf/php-fpm.conf'))
  check(pool.match?(/^\[www\]$/) && pool.match?(/^listen = 9000$/), "#{tag}: FPM pool/listener")
  dockerfile = File.read(File.join(build, 'php', 'Dockerfile'))
  entrypoint = LEGACY_VERSIONS.key?(tag) ? 'ENTRYPOINT []' : 'ENTRYPOINT ["docker-php-entrypoint"]'
  check(dockerfile.include?(entrypoint) && dockerfile.include?('CMD ["php-fpm", "-F"]'), "#{tag}: direct foreground FPM")
  check(dockerfile.include?('php-fpm -t') && dockerfile.include?('PHP_VERSION === $argv[1]'), "#{tag}: build-time validation")
  check(!dockerfile.match?(/^(?:ARG|ENV) PHP_VERSION\b/) && !dockerfile.include?('apk '), "#{tag}: preserve internal PHP version / Debian")
  check(dockerfile.include?('ENV COMPOSER_HOME=/tmp/composer'), "#{tag}: composer home")
  dockerfile.gsub(/\\\n/, ' ').scan(/^RUN (.+)$/).flatten.each do |command|
    _, err, status = Open3.capture3('sh', '-n', stdin_data: command)
    check(status.success?, "#{tag}: Dockerfile RUN shell syntax #{err}")
  end
  dockerfile.scan(/^COPY (.+)$/).flatten.each do |instruction|
    instruction.split[0...-1].each { |file| check(File.file?(File.join(build, 'php', file)), "#{tag}: COPY source #{file}") }
  end
  check(File.read(File.join(package, 'conf/php.ini')).include?('yaf.use_spl_autoload=1'), "#{tag}: retain inherited PHP settings")
  current = %w[Dockerfile configure-source install-extensions].map { |f| File.read(File.join(build, 'php', f)) }
  if LEGACY_VERSIONS.key?(tag)
    legacy_templates ||= current
    check(current == legacy_templates, "#{tag}: legacy template drift")
  else
    templates ||= current
    check(current == templates, "#{tag}: template drift")
  end
  %w[configure-source install-extensions].each do |name|
    _, err, status = Open3.capture3('sh', '-n', File.join(build, 'php', name))
    check(status.success?, "#{tag}: shell syntax #{err}")
  end
  yaml(File.join(package, 'data.yml'))
end

%w[php5 php7 php8].each do |app|
  properties = yaml(File.join(ROOT, 'apps', app, 'data.yml')).fetch('additionalProperties')
  check(properties.fetch('deprecated') == 2.0, "#{app}: preserve v1 metadata")
  architectures = app == 'php5' ? %w[amd64] : %w[amd64 arm64 arm/v7]
  check(properties.fetch('architectures') == architectures, "#{app}: supported image platforms")
end

Dir.mktmpdir('v1-php-check') do |tmp|
  bin = File.join(tmp, 'bin')
  FileUtils.mkdir_p(bin)
  %w[install-php-extensions prepare-apt].each do |name|
    path = File.join(bin, name)
    File.write(path, "#!/bin/sh\nprintf '%s\\n' \"$0\" \"$@\" > \"$CALL_LOG\"\nexit \"${STUB_EXIT:-0}\"\n")
    FileUtils.chmod(0755, path)
  end
  log = File.join(tmp, 'calls')
  shell_env = { 'PATH' => bin + File::PATH_SEPARATOR + ENV.fetch('PATH'), 'CALL_LOG' => log, 'STUB_EXIT' => '0' }
  installer = File.join(tmp, 'install-extensions')
  File.write(installer, templates[2])
  ['', 'mysqli,pdo_mysql,opcache', ' mysqli, pdo_mysql ', '-v', 'redis;id', 'redis*', 'mysqli,pdo_mysql'].each_with_index do |extensions, i|
    FileUtils.rm_f(log)
    env = shell_env.merge('PHP_EXTENSIONS' => extensions, 'STUB_EXIT' => (i == 6 ? '17' : '0'))
    _, _, status = Open3.capture3(env, 'sh', installer)
    if i == 0
      check(status.success? && !File.exist?(log), 'empty extensions must skip installer')
    elsif i < 3
      expected = i == 1 ? %w[mysqli pdo_mysql opcache] : %w[mysqli pdo_mysql]
      check(status.success? && File.readlines(log).map(&:strip).drop(1) == expected, 'comma-separated extensions must become separate arguments')
    elsif i < 6
      check(!status.success? && !File.exist?(log), 'invalid extension must not invoke installer')
    else
      check(status.exitstatus == 17, 'installer failure must propagate')
    end
  end

  check(legacy_templates[2] == templates[2], 'legacy extension installer must reuse the checked argument handling')

  source = File.join(tmp, 'configure-source')
  File.write(source, templates[1].gsub('/etc/', tmp + '/etc/').gsub('/usr/local/bin/prepare-apt', File.join(bin, 'prepare-apt')))
  FileUtils.mkdir_p(File.join(tmp, 'etc/apt/sources.list.d'))
  legacy = File.join(tmp, 'etc/apt/sources.list')
  deb822 = File.join(tmp, 'etc/apt/sources.list.d/debian.sources')
  original = "deb http://deb.debian.org/debian trixie main\ndeb https://security.debian.org/debian-security trixie-security main\n"
  original822 = "URIs: http://deb.debian.org/debian\nSuites: trixie trixie-updates\nSigned-By: /usr/share/keyrings/debian-archive-keyring.gpg\n\nURIs: https://deb.debian.org/debian-security\nSuites: trixie-security\n"
  ['mirrors.ustc.edu.cn', 'https://mirrors.tuna.tsinghua.edu.cn/debian/', 'dl-cdn.alpinelinux.org', '', 'bad|mirror', 'ftp://mirror.example', 'mirrors.ustc.edu.cn', 'mirrors.ustc.edu.cn'].each_with_index do |mirror, i|
    FileUtils.rm_f(log)
    File.write(legacy, original)
    File.write(deb822, original822)
    File.write(File.join(tmp, 'etc/os-release'), "ID=debian\nVERSION_CODENAME=#{i == 6 ? 'buster' : i == 7 ? 'bullseye' : 'trixie'}\n")
    _, _, status = Open3.capture3(shell_env.merge('CONTAINER_PACKAGE_URL' => mirror), 'sh', source)
    if i < 2
      host = i == 0 ? 'https://mirrors.ustc.edu.cn' : 'https://mirrors.tuna.tsinghua.edu.cn'
      check(status.success?, 'valid mirror should succeed')
      [legacy, deb822].each do |file|
        text = File.read(file)
        check(text.include?(host + '/debian-security') && text.include?(host + '/debian'), 'mirror must update both Debian repository types')
        check(!text.include?('/debian/debian'), 'mirror path must not duplicate Debian suffix')
      end
      check(File.read(deb822).include?('Signed-By: /usr/share/keyrings/debian-archive-keyring.gpg'), 'preserve signature verification')
    elsif i < 4
      check(status.success? && File.read(legacy) == original && File.read(deb822) == original822, 'default source should preserve Debian sources')
    elsif i < 6
      check(!status.success? && File.read(legacy) == original, 'invalid source should fail before changing sources')
    else
      check(status.success? && File.exist?(log) && File.readlines(log).first.strip.end_with?('/prepare-apt'), 'EOL bases must use inherited signed-archive helper')
      check(File.read(legacy) == original, 'active mirrors must not overwrite EOL archive configuration')
    end
  end

  # Bullseye PHP 5.6/7.0-7.2 bases do not ship prepare-apt.
  File.write(source, legacy_templates[1].gsub('/etc/', tmp + '/etc/').gsub('/usr/local/bin/prepare-apt', File.join(bin, 'prepare-apt')))
  File.write(File.join(tmp, 'etc/os-release'), "ID=debian\nVERSION_CODENAME=bullseye\n")
  FileUtils.rm_f(log)
  _, err, status = Open3.capture3(shell_env.merge('CONTAINER_PACKAGE_URL' => 'mirrors.ustc.edu.cn'), 'sh', source)
  check(status.success? && !File.exist?(log), "Bullseye must not require prepare-apt: #{err}")
  check(File.read(legacy) == "deb [check-valid-until=no] https://archive.debian.org/debian bullseye main\ndeb [check-valid-until=no] https://archive.debian.org/debian-security bullseye-security main\n", 'Bullseye must retain signed archive sources')
  File.write(File.join(tmp, 'etc/os-release'), "ID=debian\nVERSION_CODENAME=buster\n")
  _, _, status = Open3.capture3(shell_env, 'sh', source)
  check(status.success? && File.exist?(log), 'PHP 7.3 Buster must still invoke inherited prepare-apt')
end

LEGACY_VERSIONS.each do |tag, (app, internal)|
  major = tag.split('.').first
  fields = yaml(File.join(ROOT, 'apps', 'php', major, 'data.yml')).fetch('additionalProperties').fetch('formFields')
  build = yaml(File.join(ROOT, 'apps', 'php', major, 'docker-compose.yml')).fetch('services').fetch('php').fetch('build')
  check(build.fetch('args').fetch('PHP_IMAGE') == '1panel/php:${PHP_VERSION}-fpm', "#{tag}: v2 image reference")
  field = fields.find { |f| f['envKey'] == 'PHP_VERSION' }
  choice = field.fetch('values').find { |v| v['value'] == tag }
  check(choice && choice.fetch('label') == "#{tag} (1Panel patched; PHP #{internal})", "#{tag}: v2 maintenance label/baseline")
  check(field.fetch('values').none? { |v| v['value'] == internal }, "#{tag}: v2 old baseline choice")
  check(field['default'] == tag, 'PHP 5.6 default must select maintenance image') if app == 'php5'
end

puts 'PASS: twelve v1 package layouts, forms, image/baseline mapping, mounts, FPM configuration, architecture scope, shell syntax, extension argument/failure behavior, mirror conversion/defaults/signature preservation, and EOL archive dispatch.'
