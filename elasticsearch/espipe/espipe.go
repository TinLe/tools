//====================================================================================
// espipe - copy index(es) from one ES cluster to an index in same/another ES cluster
//
// This requires the elastic go library from https://github.com/olivere/elastic
//
// Copyright 2015 (c) tin@le.org  http://blog.tinle.org
// Disclaimer: Use at your own risks.  This is free, open source. GPL.
//
// This is a specific use case similar to using logstash and following config:
// logstash {
//   input { elasticsearch { ... } }
//   output { elasticsearch { ... } }
// }
//
package main

import (
	"flag"
	"fmt"
	"gopkg.in/olivere/elastic.v2"
	"time"
)

var (
	ProgramVersion = "\n\nespipe v1.0.0\n"
	help           = flag.String("help", "", "Print out this usage message")
	src            = flag.String("src", "http://localhost:9200", "Source ES cluster")
	dst            = flag.String("dst", "http://localhost:9200", "Destination ES cluster")
	sidx           = flag.String("sidx", "*", "Source index(es) to copy")
	tidx           = flag.String("tidx", "copyidx", "Target index to copy")
	bulksize       = flag.Int("bulksize", 500, "Number of docs to send to ES per chunk")
	retries        = flag.Int("retries", 3, "Number of retries 'action' before we return error")
	action         = flag.String("action", "reindex", "Action to perform: reindex, copy")
	progressflg    = flag.Bool("progressflg", true, "Display progress")
)

func Reindex(src, dst string, bsize, retries int, sourceIndexName, targetIndexName string) (count int, err error) {
	// Create a src client
	sourceClient, err := elastic.NewClient(
		elastic.SetURL(src),
		elastic.SetSniff(false),
		elastic.SetMaxRetries(retries))
	if err != nil {
		// Handle error
		fmt.Printf("Unable to connect to src: %s, err: %s", src, err)
	}

	// Create a dst client
	targetClient, err := elastic.NewClient(
		elastic.SetURL(dst),
		elastic.SetSniff(false),
		elastic.SetMaxRetries(retries))
	if err != nil {
		// Handle error
		fmt.Printf("Unable to connect to dst: %s, err: %s", dst, err)
	}

	// setup progress function
	sourceCount, err := sourceClient.Count(sourceIndexName).Do()
	if err != nil {
		fmt.Printf("Unable to get count of soure index (%s), err: %s", sourceIndexName, err)
	}

	tick := int64(100000)
	if *progressflg {
		fmt.Printf("sourceCount (%d)\n", sourceCount)
		if sourceCount < 1000000 {
			tick = (sourceCount % 10)
		}
	}
	t0 := time.Now()
	progress := func(current, total int64) {
		if current%tick == 0 && *progressflg {
			t1 := time.Now()
			fmt.Printf("time: %v, current: %d (%d)\n", t1.Sub(t0), current, total)
			t0 = time.Now()
		}
	}

	// Start the copy
	r := elastic.NewReindexer(sourceClient, sourceIndexName, elastic.CopyToTargetIndex(targetIndexName))
	r = r.TargetClient(targetClient).Progress(progress).BulkSize(bsize)
	startTime := time.Now()
	ret, err := r.Do()
	if err != nil {
		//t.Fatal(err)
		fmt.Printf("Error while CopyToTargetIndex(ret: %, err: %s)", ret, err)
	}
	endTime := time.Now()
	fmt.Printf("Start: %v\nEnd: %v\nSourceCount: %d\n", startTime, endTime, sourceCount)

	return 1, err
}

func main() {
	flag.Parse()
	if *sidx == "*" {
		fmt.Printf("%s\n", ProgramVersion)
		flag.PrintDefaults()
		fmt.Printf("\n\n")
		return
	}
	fmt.Println(Reindex(*src, *dst, *bulksize, *retries, *sidx, *tidx))
}
