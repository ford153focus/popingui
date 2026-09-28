using System.Collections.Concurrent;
using System.Globalization;
using System.Net.NetworkInformation;
using Spectre.Console;

class Program
{
    static bool keepRunning = true;

    static readonly ConcurrentDictionary<string,HostPingStatictics> storage = [];

    public static async Task MakePingAsync(string host, int timeout)
    {
        using Ping pingSender = new();

        while (true)
        {
            try
            {
                PingReply pingReply = await pingSender.SendPingAsync(host, timeout);

                if (pingReply.Status != IPStatus.Success) throw new Exception();

                if (pingReply.Status == IPStatus.Success)
                {
                    storage[host].ComputedAddress = pingReply.Address.ToString();
                    storage[host].ResponseTime = pingReply.RoundtripTime;
                    storage[host].Bytes = pingReply.Buffer.Length;
                    storage[host].TimeToLive = pingReply.Options?.Ttl ?? -1;

                    storage[host].TotalResponseTime += pingReply.RoundtripTime;

                    if (pingReply.RoundtripTime > storage[host].MaxResponseTime) storage[host].MaxResponseTime = pingReply.RoundtripTime;
                    if (pingReply.RoundtripTime < storage[host].MinResponseTime) storage[host].MinResponseTime = pingReply.RoundtripTime;
                }
                else
                {
                    storage[host].ComputedAddress = $"[red]{nameof(pingReply.Status)}[/]";
                    storage[host].ResponseTime = -2;
                    storage[host].Bytes = -2;
                    storage[host].TimeToLive = -2;
                    storage[host].LostPackages +=1;
                }
            }
            catch (Exception)
            {
                storage[host].ComputedAddress = "[red]Exception![/]";
                storage[host].ResponseTime = -2;
                storage[host].Bytes = -2;
                storage[host].TimeToLive = -2;
                storage[host].LostPackages +=1;
            }
            finally
            {
                storage[host].SentPackages += 1;
                await Task.Delay(3510);
            }
        }
    }

    public static async Task Main(string[] args)
    {
        Console.Clear();

        List<HostConfig> nodes = Config.Load(args.FirstOrDefault());

        #region CTRL+C HANDLER SETUP
        Console.CancelKeyPress += (s, e) =>
        {
            e.Cancel = true; // Prevent immediate exit
            keepRunning = false;
        };
        #endregion

        #region INIT STORAGE + CREATE PING TASKS
        ConcurrentBag<Task> pingTasks = [];

        foreach (HostConfig item in nodes)
        {
            storage[item.Address] = new HostPingStatictics(){
                TargetHost = item.Address,
                Description = item.Description,
            };

            pingTasks.Add(Task.Run(async () => await MakePingAsync(item.Address, item.Timeout)));
        }
        #endregion

        #region SETUP THE UI TABLE
        Table table = new Table().Border(TableBorder.Rounded)
                                 .AddColumn("host")
                                 .AddColumn("description")
                                 .AddColumn("address")
                                 .AddColumn("time")
                                 .AddColumn("avg time")
                                 .AddColumn("min time")
                                 .AddColumn("max time")
                                 .AddColumn("sent")
                                 .AddColumn("lost")
                                 .AddColumn("availability")
                                 .AddColumn("bytes")
                                 .AddColumn("ttl");
        #endregion

        #region Run the Live TUI
        await AnsiConsole.Live(table)
            .AutoClear(false)
            .StartAsync(async ctx =>
            {
                while (keepRunning)
                {
                    table.Rows.Clear(); // Clear old rows

                    foreach (HostConfig node in nodes)
                    {
                        // table.UpdateCell(item.Id, 1, item.TargetHost);
                        // table.UpdateCell(item.Id, 2, item.ComputedAddress);
                        // table.UpdateCell(item.Id, 3, item.ResponseTime.ToString("F2"));
                        // table.UpdateCell(item.Id, 4, item.AverageResponseTime.ToString());
                        // table.UpdateCell(item.Id, 5, item.TotalResponseTime.ToString("F2"));
                        // table.UpdateCell(item.Id, 6, item.SentPackages.ToString());
                        // table.UpdateCell(item.Id, 7, item.LostPackages.ToString());
                        // table.UpdateCell(item.Id, 8, item.Availability.ToString());
                        // table.UpdateCell(item.Id, 9, item.Bytes.ToString());
                        // table.UpdateCell(item.Id, 10, item.TimeToLive.ToString());

                        table.AddRow(
                            storage[node.Address].TargetHost,
                            storage[node.Address].Description,
                            storage[node.Address].ComputedAddress,
                            storage[node.Address].ResponseTime == -2 ? "[red]failed[/]" : storage[node.Address].ResponseTime.ToString()+" ms",
                            storage[node.Address].AverageResponseTime == -1 ? "[grey]no data[/]" : storage[node.Address].AverageResponseTime.ToString("F3", CultureInfo.InvariantCulture)+" ms",
                            storage[node.Address].MinResponseTime == Int32.MaxValue ? "[grey]no data[/]" : storage[node.Address].MinResponseTime.ToString()+" ms",
                            storage[node.Address].MaxResponseTime == -1 ? "[grey]no data[/]" : storage[node.Address].MaxResponseTime.ToString()+" ms",
                            storage[node.Address].SentPackages.ToString(),
                            storage[node.Address].LostPackages.ToString(),
                            storage[node.Address].Availability.ToString("F3", CultureInfo.InvariantCulture)+"%",
                            storage[node.Address].Bytes == -2 ? "[red]failed[/]" : storage[node.Address].Bytes.ToString(),
                            storage[node.Address].TimeToLive == -2 ? "[red]failed[/]" : storage[node.Address].TimeToLive.ToString()
                        );
                    }

                    ctx.Refresh(); // Force the console to redraw the table
                    await Task.Delay(1234); // Wait before next iteration
                }
            });
        #endregion
    }
}
