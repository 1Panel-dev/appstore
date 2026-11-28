# frozen_string_literal: true

require "json"
require "open3"
require "yaml"

templates = Dir[File.expand_path("../apps/affine/*/docker-compose.yml", __dir__)]
abort "No AFFiNE templates found" if templates.empty?

templates.each do |compose|
  fields = YAML.load_file(File.join(File.dirname(compose), "data.yml")).fetch("additionalProperties").fetch("formFields")
  password = fields.find { |field| field["envKey"] == "PANEL_REDIS_ROOT_PASSWORD" }
  abort "#{compose}: expected a read-only password field" unless password && password["type"] == "password" && password["disabled"] == true

  [nil, "6380"].each do |port|
    env = {
      "CONTAINER_NAME" => "affine-test", "PANEL_APP_PORT_HTTP" => "3010",
      "PANEL_REDIS_HOST" => "cache.example", "PANEL_REDIS_PORT" => port, "PANEL_REDIS_ROOT_PASSWORD" => "test",
      "PANEL_DB_HOST" => "db.example", "PANEL_DB_PORT" => "5432", "PANEL_DB_NAME" => "test",
      "PANEL_DB_USER" => "test", "PANEL_DB_USER_PASSWORD" => "test"
    }
    output, err, status = Open3.capture3(env, "docker", "compose", "-p", "affine-test", "-f", compose, "config", "--format", "json")
    abort err unless status.success?
    services = JSON.parse(output).fetch("services")
    %w[affine affine_migration].each do |name|
      actual = services.fetch(name).fetch("environment").fetch("REDIS_SERVER_PORT")
      abort "#{compose}: #{name} received port #{actual}" unless actual == (port || "6379")
    end
    abort "#{compose}: migration must run once" unless services.fetch("affine_migration").fetch("restart", "no") == "no"
  end
  puts "#{File.basename(File.dirname(compose))}: OK"
end
