class HostPingStatictics
{
    public required string TargetHost { get; set; }
    public required string Description { get; set; }
    public string ComputedAddress { get; set; } = "";
    public long ResponseTime { get; set; } = -1;
    public double AverageResponseTime {get {
        if (GotPackages == 0) return -1;
        return TotalResponseTime / GotPackages;
    }}
    public long MinResponseTime { get; set; } = Int32.MaxValue;
    public long MaxResponseTime { get; set; } = -1;
    public double TotalResponseTime { get; set; } = 0;
    public int SentPackages { get; set; } = 0;
    public int LostPackages { get; set; } = 0;
    public int GotPackages {get {
        return SentPackages - LostPackages;
    }}
    public double Availability { get {
        if (GotPackages == 0) return 0;
        double ratio = (double)GotPackages / (double)SentPackages;
        return ratio*100;
    }}
    public int Bytes { get; set; } = 0;
    public int TimeToLive { get; set; } = 0;
}
