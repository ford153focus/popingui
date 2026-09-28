using System.Text.Json;
using System.Text.Json.Serialization;

class Config
{
    public const string FileName = "config.json";

    /// <summary>
    /// Reads the hosts from <see cref="FileName"/>.
    /// The file is looked up at the given path (when provided), then in the
    /// current working directory and finally next to the executable.
    /// </summary>
    public static List<HostConfig> Load(string? path = null)
    {
        string fullPath = ResolvePath(path);

        List<HostConfig> hosts;
        using (FileStream stream = File.OpenRead(fullPath))
        {
            hosts = JsonSerializer.Deserialize(stream, ConfigJsonContext.Default.ListHostConfig)
                    ?? throw new InvalidDataException($"Can't parse {fullPath}");
        }

        for (int i = 0; i < hosts.Count; i++)
        {
            if (string.IsNullOrWhiteSpace(hosts[i].Address))
                throw new InvalidDataException($"Host #{i + 1} in {fullPath} has no \"address\"");
        }

        return [.. hosts.Where(host => host.Enabled)];
    }

    private static string ResolvePath(string? path)
    {
        if (!string.IsNullOrWhiteSpace(path))
        {
            return File.Exists(path) ? path : throw new FileNotFoundException($"Config file not found: {path}");
        }

        string[] candidates =
        [
            Path.Combine(Directory.GetCurrentDirectory(), FileName),
            Path.Combine(AppContext.BaseDirectory, FileName),
        ];

        foreach (string candidate in candidates)
        {
            if (File.Exists(candidate)) return candidate;
        }

        throw new FileNotFoundException($"No {FileName} found. Looked in: {string.Join(", ", candidates)}");
    }
}

[JsonSourceGenerationOptions(
    PropertyNamingPolicy = JsonKnownNamingPolicy.CamelCase, 
    PropertyNameCaseInsensitive = true, 
    WriteIndented = true
)]
[JsonSerializable(typeof(List<HostConfig>))]
internal partial class ConfigJsonContext : JsonSerializerContext
{
}
