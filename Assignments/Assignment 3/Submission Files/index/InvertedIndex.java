import java.io.IOException;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.StringTokenizer;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;

public class InvertedIndexJob
{
  
   public static class InvertedIndexMapper extends Mapper <LongWritable,Text,Text,Text> 
   {
      private Text word = new Text();
      Text id = new Text();

      //Mapper
      public void map(LongWritable key, Text value, Context context) throws IOException, InterruptedException
      {
        String input = value.toString();
       	String inputArray[] = input.split("\\t", 2);
       	String docid = inputArray[0];
       	id.set(docid);
       	String line = inputArray[1].toLowerCase().replaceAll("[^a-z]+"," ");
       	StringTokenizer tokens = new StringTokenizer(line);
       	while (tokens.hasMoreTokens()) 
  	    {
        	  word.set(tokens.nextToken());
        	  context.write(word, id);
       	}
      }
   }

   
   public static class InvertedIndexReducer extends Reducer <Text,Text,Text,Text> 
   {
    // Reducer
    public void reduce(Text key, Iterable<Text> values,Context context) throws IOException,InterruptedException
    {
       HashMap <String, Integer> myMap = new HashMap <String, Integer>();
       int frequency = 0;
       String value;
       Iterator <Text> itr = values.iterator();

       while (itr.hasNext())
       {
          value = itr.next().toString();
          myMap.put(value, myMap.getOrDefault(value,0)+1);
       }

       StringBuilder sb = new StringBuilder("");
       for (String w: myMap.keySet()) 
          sb.append(w + ":" + myMap.get(w) + "\t");
       context.write(key, new Text(sb.toString()));
      }
   }

   // Main function
   public static void main(String[] args) throws Exception 
   {
      Configuration conf = new Configuration();
      Job myJob = Job.getInstance(conf, "inverted index");
      myJob.setJarByClass(InvertedIndexJob.class);
      myJob.setMapperClass(InvertedIndexMapper.class);
      myJob.setReducerClass(InvertedIndexReducer.class);
      myJob.setOutputKeyClass(Text.class);
      myJob.setOutputValueClass(Text.class);
      FileInputFormat.addInputPath(myJob, new Path(args[0]));
      FileOutputFormat.setOutputPath(myJob, new Path(args[1]));
      System.exit(myJob.waitForCompletion(true) ? 0 : 1);
   }
}