"use strict";

const fs = require("fs");
const ttest = require("ttest");

const VALID_GROUP_BYS = ["browser", "pdf", "page", "round", "stat"];

function parseOptions() {
  const yargs = require("yargs")
    .usage(
      "Compare the results of two stats files.\n" +
        "Usage:\n  $0 <BASELINE> <CURRENT> [options]"
    )
    .demand(2)
    .string(["groupBy"])
    .describe(
      "groupBy",
      "How statistics should grouped. Valid options: " +
        VALID_GROUP_BYS.join(" ")
    )
    .default("groupBy", "browser,stat");
  const result = yargs.argv;
  result.baseline = result._[0];
  result.current = result._[1];
  if (result.groupBy) {
    result.groupBy = result.groupBy.split(/[;, ]+/);
  }
  return result;
}

function group(stats, groupBy) {
  const vals = [];
  for (const curStat of stats) {
    const keyArr = [];
    for (const entry of groupBy) {
      keyArr.push(curStat[entry]);
    }
    const key = keyArr.join(",");
    (vals[key] ||= []).push(curStat.time);
  }
  return vals;
}

/*
 * Flatten the stats so that there's one row per stats entry.
 * Also, if results are not grouped by 'stat', keep only 'Overall' results.
 */
function flatten(stats) {
  let rows = [];
  stats.forEach(function (curStat) {
    curStat.stats.forEach(function (s) {
      rows.push({
        browser: curStat.browser,
        page: curStat.page,
        pdf: curStat.pdf,
        round: curStat.round,
        stat: s.name,
        time: s.end - s.start,
      });
    });
  });
  // Use only overall results if not grouped by 'stat'
  if (!options.groupBy.includes("stat")) {
    rows = rows.filter(function (s) {
      return s.stat === "Overall";
    });
  }
  return rows;
}

function pad(s, length, dir /* default: 'right' */) {
  s = "" + s;
  const spaces = new Array(Math.max(0, length - s.length + 1)).join(" ");
  return dir === "left" ? spaces + s : s + spaces;
}

function mean(array) {
  function add(a, b) {
    return a + b;
  }
  return array.reduce(add, 0) / array.length;
}

/* Comparator for row key sorting. */
function compareRow(a, b) {
  a = a.split(",");
  b = b.split(",");
  for (let i = 0; i < Math.min(a.length, b.length); i++) {
    const intA = parseInt(a[i], 10);
    const intB = parseInt(b[i], 10);
    const ai = isNaN(intA) ? a[i] : intA;
    const bi = isNaN(intB) ? b[i] : intB;
    if (ai < bi) {
      return -1;
    }
    if (ai > bi) {
      return 1;
    }
  }
  return 0;
}

/*
 * Dump various stats in a table to compare the baseline and current results.
 * T-test Refresher:
 * If I understand t-test correctly, p is the probability that we'll observe
 * another test that is as extreme as the current result assuming the null
 * hypothesis is true. P is NOT the probability of the null hypothesis. The null
 * hypothesis in this case is that the baseline and current results will be the
 * same. It is generally accepted that you can reject the null hypothesis if the
 * p-value is less than 0.05. So if p < 0.05 we can reject the results are the
 * same which doesn't necessarily mean the results are faster/slower but it can
 * be implied.
 */
function stat(baseline, current) {
  const baselineGroup = group(baseline, options.groupBy);
  const currentGroup = group(current, options.groupBy);

  const keys = Object.keys(baselineGroup);
  keys.sort(compareRow);

  const labels = options.groupBy.slice(0);
  labels.push("Count", "Baseline(ms)", "Current(ms)", "+/-", "% ");
  if (ttest) {
    labels.push("Result(P<.05)");
  }
  const rows = [];
  // collect rows and measure column widths
  const width = labels.map(function (s) {
    return s.length;
  });
  rows.push(labels);
  for (const key of keys) {
    const baselineMean = mean(baselineGroup[key]);
    const currentMean = mean(currentGroup[key]);
    const row = key.split(",");
    row.push(
      "" + baselineGroup[key].length,
      "" + Math.round(baselineMean),
      "" + Math.round(currentMean),
      "" + Math.round(currentMean - baselineMean),
      ((100 * (currentMean - baselineMean)) / baselineMean).toFixed(2)
    );
    if (ttest) {
      const p =
        baselineGroup[key].length < 2
          ? 1
          : ttest(baselineGroup[key], currentGroup[key]).pValue();
      if (p < 0.05) {
        row.push(currentMean < baselineMean ? "faster" : "slower");
      } else {
        row.push("");
      }
    }
    for (let i = 0; i < row.length; i++) {
      width[i] = Math.max(width[i], row[i].length);
    }
    rows.push(row);
  }

  // add horizontal line
  const hline = width.map(function (w) {
    return new Array(w + 1).join("-");
  });
  rows.splice(1, 0, hline);

  // print output
  console.log("-- Grouped By " + options.groupBy.join(", ") + " --");
  const groupCount = options.groupBy.length;
  for (const row of rows) {
    for (let i = 0; i < row.length; i++) {
      row[i] = pad(row[i], width[i], i < groupCount ? "right" : "left");
    }
    console.log(row.join(" | "));
  }
}

function main() {
  let baseline, current;
  try {
    const baselineFile = fs.readFileSync(options.baseline).toString();
    baseline = flatten(JSON.parse(baselineFile));
  } catch (e) {
    console.log('Error reading file "' + options.baseline + '": ' + e);
    process.exit(0);
  }
  try {
    const currentFile = fs.readFileSync(options.current).toString();
    current = flatten(JSON.parse(currentFile));
  } catch (e) {
    console.log('Error reading file "' + options.current + '": ' + e);
    process.exit(0);
  }
  stat(baseline, current);
}

const options = parseOptions();
main();
