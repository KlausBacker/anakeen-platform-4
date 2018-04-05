<?php

namespace Dcp\Utils\CSVFormatDetector;

class SampleAccumulator
{
    /**
     * @var Sample[]
     */
    public $samples = array();
    
    public function add($str, $count, $weight = 1)
    {
        $this->samples[] = new Sample($str, $count, $weight);
    }
    
    public function updateScores()
    {
        $totalWeight = 0;
        foreach ($this->samples as & $sample) {
            $totalWeight+= $sample->weight;
        }
        unset($sample);
        foreach ($this->samples as & $sample) {
            if ($totalWeight == 0) {
                $sample->score = 0;
            } else {
                $sample->score = $sample->weight / $totalWeight;
            }
        }
        unset($sample);
    }
    
    public function getMergedSamples()
    {
        $this->updateScores();
        $this->sortByScore($this->samples);
        $mergedSamples = array();
        foreach ($this->samples as & $sample) {
            if (!isset($mergesSamples[$sample->str])) {
                $mergesSamples[$sample->str] = array();
            }
            if (isset($mergedSamples[$sample->str]->str)) {
                $mergedSamples[$sample->str]->count+= $sample->count;
                $mergedSamples[$sample->str]->score+= $sample->score;
            } else {
                $mergedSamples[$sample->str] = $sample;
            }
        }
        unset($sample);
        $this->sortByScore($mergedSamples);
        return array_values($mergedSamples);
    }
    /**
     * Return sample with highest confidence score or NULL if there is no samples
     *
     * @return null|Sample
     *
     */
    public function getCandidate($minConfidence = 0)
    {
        $samples = $this->getMergedSamples();
        $samples = array_filter($samples, function (Sample & $sample) use ($minConfidence) {
            return ($sample->score >= $minConfidence);
        });
        if (count($samples) <= 0) {
            return null;
        }
        return $samples[0];
    }
    protected function sortByScore(&$samples)
    {
        uasort($samples, function (Sample $a, Sample $b) {
            if ($a->score == $b->score) {
                return 0;
            }
            return ($a->score < $b->score) ? 1 : -1;
        });
    }
    
    public function dump($merged = true)
    {
        if ($merged) {
            $samples = $this->getMergedSamples();
        } else {
            $this->updateScores();
            $samples = $this->samples;
        }
        foreach ($samples as & $sample) {
            printf("\t{[%s], %s, %s}\n", $sample->str, $sample->count, $sample->score);
        }
        unset($sample);
    }
}

