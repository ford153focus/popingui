#!/usr/bin/python3

__author__ = 'ford153focus'

import curses
import json
import re
import subprocess
from sys import exit
from sys import argv
from os import path
from threading import Thread
from time import sleep


def global_variables_initialization():
	"""
	init some global vars that used in every function
	i know that global vars is bad practice. global vars was introduced in this program in attempt to reduce memory usage.
	not helped. later i got to know why. but too lazy to remake all is back.
	:return: void
	"""
	global stats
	global grid
	global settings
	global threads

	settings = json.load(open(path.dirname(argv[0])+"/settings.json"))

	targets = json.load(open(path.dirname(argv[0])+"/targets.json"))

	header = [
		"Description",
		"IP (specified)",
		"IP (determined)",
		"PACKET SIZE",
		"PING (current)",
		"PING (average)",
		"PING (minimal)",
		"PING (maximal)",
		"TTL",
		"PACKETS (sent)",
		"PACKETS (lost)",
		"PACKETS (lost %)"
	]

	stats = [
		{
			"description": targets[_]["description"],
			"ip": {
				"specified": targets[_]["address"],
				"determined": ""
			},
			"packet_size": int(targets[_]["packet_size"]),
			"ping": {
				"current": 0,
				"amount": 0,
				"average": 0,
				"minimal": -1,
				"maximal": 0,
			},
			"ttl": 0,
			"packets": {
				"sent": 0,
				"lost": 0,
				"lost_percentage": 0
			}
		} for _ in range(len(targets))]  # here we will store all statistics

	# init grid
	grid = [[curses.newwin(0, 0, 0, 0) for __ in range(len(header))] for _ in range(len(targets) + 1)]  # here we will store array of curses' windows

	# create curses' windows and draw borders
	for _ in range(len(targets) + 1):
		for __ in range(len(header)):
			# print(type((int(settings["cell_size"]["width"]) - 1) * __))
			# exit()
			grid[_][__] = curses.newwin(
				int(settings["cell_size"]["height"]),
				int(settings["cell_size"]["width"]),
				(int(settings["cell_size"]["height"]) - 1) * _,
				(int(settings["cell_size"]["width"]) - 1) * __
			)
			grid[_][__].border(0, 0, 0, 0, 0, 0, 0, 0)
			grid[_][__].refresh()

	# add headers to grid
	for _ in range(len(header)):
		grid[0][_].addstr(1, 1, header[_])
		grid[0][_].refresh()

	# fill static fields from targets
	for _ in range(len(targets)):
		grid[_ + 1][0].addstr(1, 1, str(targets[_]["description"]))
		grid[_ + 1][0].refresh()
		grid[_ + 1][1].addstr(1, 1, str(targets[_]["address"]))
		grid[_ + 1][1].refresh()
		grid[_ + 1][3].addstr(1, 1, str(targets[_]["packet_size"]))
		grid[_ + 1][3].refresh()

	threads = [True for _ in range(len(targets))]  # here we will store threads


def ping(row_pointer):
	"""
	do ping, parse answer and fill dictionary
	:param row_pointer: integer
	:return: void
	"""
	try:
		shell_answer = subprocess.Popen(
			[
				"ping",
				"-c 1",
				"-W " + str(settings["ping"]["timeout"]),
				"-s " + str(stats[row_pointer]["packet_size"]) if stats[row_pointer]["packet_size"] else str(settings["ping"]["default_packet_size"]),
				stats[row_pointer]["ip"]["specified"]
			],
			stdout=subprocess.PIPE
		).stdout.read()
		shell_answer = str(shell_answer)
	except subprocess.CalledProcessError:
		shell_answer = "100% packet loss"

	stats[row_pointer]["packets"]["sent"] += 1  # increase count of sent packets

	if shell_answer.find("100% packet loss") == -1 and shell_answer.find("Network is unreachable") == -1:  # ping is failed or not
		try:
			# get real IP
			stats[row_pointer]["ip"]["determined"] = re.search("PING.+\s\((\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\)\s", shell_answer, flags=re.MULTILINE).group(1)
			# get ping time
			stats[row_pointer]["ping"]["current"] = float(re.search("time=(\d{1,4}(\.\d{1,3})?)\s", shell_answer, flags=re.MULTILINE).group(1))
			# get ttl
			stats[row_pointer]["ttl"] = re.search("ttl=(\d{1,3})\s", shell_answer, flags=re.MULTILINE).group(1)
			# average ping
			stats[row_pointer]["ping"]["amount"] += stats[row_pointer]["ping"]["current"]
			stats[row_pointer]["ping"]["average"] = stats[row_pointer]["ping"]["amount"] / stats[row_pointer]["packets"]["sent"]
			# is this ping have max time?
			if stats[row_pointer]["ping"]["current"] > stats[row_pointer]["ping"]["maximal"]:
				stats[row_pointer]["ping"]["maximal"] = stats[row_pointer]["ping"]["current"]
			# is this ping have minimal time?
			if stats[row_pointer]["ping"]["minimal"] == -1:
				stats[row_pointer]["ping"]["minimal"] = stats[row_pointer]["ping"]["current"]
			elif stats[row_pointer]["ping"]["current"] < stats[row_pointer]["ping"]["minimal"]:
				stats[row_pointer]["ping"]["minimal"] = stats[row_pointer]["ping"]["current"]
		except Exception as ex1:
			# f = open(path.dirname(argv[0])+"/error.log", 'w')
			# f.write(str(shell_answer))
			# f.write("\n\n")
			# f.write(str(ex1))
			# f.write("\n\n")
			# f.close()
			stats[row_pointer]["packets"]["lost"] += 1  # increase count of lost packets
	else:
		stats[row_pointer]["packets"]["lost"] += 1  # increase count of lost packets

	stats[row_pointer]["packets"]["lost_percentage"] = stats[row_pointer]["packets"]["lost"] / stats[row_pointer]["packets"]["sent"] * 100  # recalculate percentage of lost packets


def update_grid_all():
	"""
	update table
	:return: void
	"""
	for _ in range(len(stats)):
		tmp_stat = [
			False,
			False,
			str(stats[_]["ip"]["determined"]),
			False,
			str(stats[_]["ping"]["current"]),
			str(round(stats[_]["ping"]["average"], 3)),
			str(stats[_]["ping"]["minimal"]),
			str(stats[_]["ping"]["maximal"]),
			str(stats[_]["ttl"]),
			str(stats[_]["packets"]["sent"]),
			str(stats[_]["packets"]["lost"]),
			str(round(stats[_]["packets"]["lost_percentage"], 1))+" %"
		]
		for __ in [2, 4, 5, 6, 7, 8, 9, 10, 11]:
			grid[_+1][__].clear()  # clear window
			grid[_+1][__].border(0, 0, 0, 0, 0, 0, 0, 0)  # redraw border
			grid[_+1][__].addstr(1, 1, tmp_stat[__])  # draw string
			grid[_+1][__].refresh()  # refresh window


def row_processor(row_pointer):
	"""
	infinity cycle for periodical ping for 1 host
	:param row_pointer: integer
	:return:
	"""
	while True:
		ping(row_pointer)
		sleep(int(settings["ping"]["interval"]))


def main():
	# ncurses init
	curses.initscr()
	# main window
	main_window = curses.newwin(0, 0, 0, 0)
	main_window.border(0, 0, 0, 0, 0, 0, 0, 0)
	main_window.refresh()

	global_variables_initialization()

	# create threads
	for _ in range(len(stats)):
		threads[_] = Thread(target=row_processor, args=([_]))
		threads[_].daemon = True
		threads[_].start()

	# infinity cycle for periodical table update, Ctrl+C-event and program keep-alive
	while True:
		try:
			sleep(int(settings["ping"]["interval"]))
			update_grid_all()
		except KeyboardInterrupt:
			# curses.endwin()
			exit()

if __name__ == "__main__":
	main()