//====================================================================================
// espipe - copy index(es) from one ES cluster to an index in same/another ES cluster
// 
// This requires the elastic go library from https://github.com/olivere/elastic
//
// tin@le.org  http://blog.tinle.org
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
    "time"
    "github.com/olivere/elastic"
)

var (
      src = flag.String("src", "http://localhost:9200", "Source ES cluster (default to http://localhost:9200)")
      dst = flag.String("dst", "http://localhost:9200", "Destination ES cluster (default to http://localhost:9200)")
      sidx = flag.String("sidx", "logstash*", "Source index(es) to copy (default to all '*')")
      tidx = flag.String("tidx", "copyidx", "Target index to copy (default to 'copyidx')")
      bulksize = flag.Int("bulksize", 500, "Number of docs to send to ES per chunk (default to 500)")
)

func Reindex(src, dst string, bsize int, sourceIndexName, targetIndexName string) (count int, err error) {
  // Create a src client
  sourceClient, err := elastic.NewClient(elastic.SetURL(src))
  if err != nil {
    // Handle error
    fmt.Printf("Unable to connect to src: %s, err: %s", src, err)
  }

  // Create a dst client
  targetClient, err := elastic.NewClient(elastic.SetURL(dst))
  if err != nil {
    // Handle error
    fmt.Printf("Unable to connect to dst: %s, err: %s", dst, err)
  }

  // setup progress function
  sourceCount, err := sourceClient.Count(sourceIndexName).Do()
  if err != nil {
    fmt.Printf("Unable to get count of soure index (%s), err: %s", sourceIndexName, err)
  }

  totalsOk := true
  t0 := time.Now()
  progress := func(current, total int64) {
    totalsOk = totalsOk && total == sourceCount
    if (current % 100000 == 0) {
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
  fmt.Println(Reindex(*src, *dst, *bulksize, *sidx, *tidx))
}

