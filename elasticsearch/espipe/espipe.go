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
	"context"
	"flag"
	"fmt"
	"log"
	"strings"
	"time"
	elasticsearch5 "github.com/elastic/go-elasticsearch/v5"
	elasticsearch6 "github.com/elastic/go-elasticsearch/v6"
	elasticsearch7 "github.com/elastic/go-elasticsearch/v7"
)

var (
	ProgramVersion = "espipe v1.0.1\n"
	version        = flag.Bool("version", false, ProgramVersion)
	src            = flag.String("src", "http://localhost:9200", "Source ES cluster")
	dst            = flag.String("dst", "http://localhost:9200", "Destination ES cluster")
	sidx           = flag.String("sidx", "*", "Source index(es) to copy")
	tidx           = flag.String("tidx", "copyidx", "Target index to copy")
	bulksize       = flag.Int("bulksize", 500, "Number of docs to send to ES per chunk")
	retries        = flag.Int("retries", 3, "Number of retries 'action' before we return error")
	progressflg    = flag.Bool("progressflg", false, "Display progress")
	debug          = flag.Bool("debug", false, "Display debugging messages")
)

func Reindex(src, dst string, bsize, retries int, sourceIndexName, targetIndexName string) (count int, err error) {

	// Setup client config
	cfg6 = elasticsearch5.Config{
		Addresses: []string{ src },

	}
	// Create a src client
	e5sourceClient, err := elasticsearch5.NewDefaultClient(
		elastic.SetURL(src),
		elastic.SetSniff(true),
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

    // Set some flags first
    DisableWrite = '{"index.blocks.write": true}'
    settings, err := sourceClient.IndexPutSettings().
        Index(sourceIndexName).
        BodyString(DisableWrite).
        Do(context.TODO())
    if err != nill {
		fmt.Printf("Unable to set flags on index : %s, err: %s", sourceIndexName, err)
    }
    if settings.Acknowledged {
		fmt.Printf("Successfully DisableWrite on index : %s, err: %s", sourceIndexName, err)
    }

	// setup progress function
	sourceCount, err := sourceClient.Count(sourceIndexName).Do()
	if err != nil {
		fmt.Printf("Unable to get count of source index (%s), err: %s", sourceIndexName, err)
	}

	tick := int64(100000)
	if *progressflg {
		fmt.Printf("sourceCount (%d)\n", sourceCount)
		if sourceCount < 1000000 {
			tick = (sourceCount % 100000)
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
	// r := elastic.NewReindexer(sourceClient, sourceIndexName, elastic.CopyToTargetIndex(targetIndexName))
	r := elastic.Reindex.WaitForActiveShards("all").
        SourceIndex.(sourceClient).
        DestinationIndex(sourceIndexName, elastic.CopyToTargetIndex(targetIndexName))
	r = r.TargetClient(targetClient).Progress(progress).BulkSize(bsize)
	startTime := time.Now()
	ret, err := r.Do()
	if err != nil {
		//t.Fatal(err)
		fmt.Printf("Error while CopyToTargetIndex(ret: %d, err: %s)", ret, err)
	}
	endTime := time.Now()
	fmt.Printf("Start: %v\nEnd: %v\nSourceCount: %d\n", startTime, endTime, sourceCount)

	return 1, err
}

func main() {
	flag.Parse()
	if *sidx == "*" {
		fmt.Printf("\n\n%s\n", ProgramVersion)
		flag.PrintDefaults()
		fmt.Printf("\n\n")
		return
	}
	if strings.HasPrefix(*src, "http://") != true {
		s := []string{"http:", *src}
		*src = strings.Join(s, "//")
	}
	if strings.HasPrefix(*dst, "http://") != true {
		s := []string{"http:", *dst}
		*dst = strings.Join(s, "//")
	}
	if *debug {
		fmt.Printf("\n\nDebug output.....\n")
		fmt.Printf("Source Elasticsearch = %s\nDestination Elasticsearch = %s\n", *src, *dst)
		fmt.Printf("Source Index = %s\nDestination Index = %s\n", *sidx, *tidx)
		fmt.Printf("How many Retries = %d\nBulk index size = %d\n\n", *retries, *bulksize)
	}
	ret, err := Reindex(*src, *dst, *bulksize, *retries, *sidx, *tidx)
	if err != nil {
		//t.Fatal(err)
		fmt.Printf("Error while Reindexing: ret: %d, err: %s)", ret, err)
	}
}
