<?php
/**
 * The model file of badge module of ZenTaoPMS (WenYinOS community build).
 *
 * 公开只读的 SVG 徽章：供 README 等外部页面嵌入，展示 openBugs / openStory / latestRelease。
 * 只输出聚合数字与版本名，不暴露标题、指派人等内部字段。
 *
 * @package badge
 */
class badgeModel extends model
{
    /**
     * Render a badge as SVG.
     *
     * @param  array  $params  $_GET
     * @access public
     * @return string
     */
    public function render($params)
    {
        /* 可在 config/my.php 里设 $config->badge->enabled = false 关闭公开徽章。 */
        if(isset($this->config->badge->enabled) and !$this->config->badge->enabled) return $this->svg('zentao', 'disabled', '#9f9f9f');

        $type    = isset($params['type']) ? strtolower(trim((string)$params['type'])) : 'openbugs';
        $product = isset($params['product']) ? (int)$params['product'] : 0;

        switch($type)
        {
            case 'openbugs':
                return $this->svg('open bugs', $this->countBugs($product), '#e05d44');
            case 'openstory':
                return $this->svg('open stories', $this->countStories($product), '#007ec6');
            case 'latestrelease':
                return $this->svg('release', $this->getLatestRelease($product), '#4c1');
            default:
                return $this->svg('zentao', 'unknown', '#9f9f9f');
        }
    }

    /**
     * Count open (not closed) bugs.
     *
     * @param  int    $product
     * @access private
     * @return int
     */
    private function countBugs($product)
    {
        $sql = $this->dao->select('COUNT(*) AS count')->from(TABLE_BUG)
            ->where('deleted')->eq('0')
            ->andWhere('status')->ne('closed');
        if($product) $sql->andWhere('product')->eq($product);
        return (int)$sql->fetch('count');
    }

    /**
     * Count open (not closed) stories.
     *
     * @param  int    $product
     * @access private
     * @return int
     */
    private function countStories($product)
    {
        $sql = $this->dao->select('COUNT(*) AS count')->from(TABLE_STORY)
            ->where('deleted')->eq('0')
            ->andWhere('status')->ne('closed');
        if($product) $sql->andWhere('product')->eq($product);
        return (int)$sql->fetch('count');
    }

    /**
     * Get the latest release name.
     *
     * @param  int    $product
     * @access private
     * @return string
     */
    private function getLatestRelease($product)
    {
        $sql = $this->dao->select('name')->from(TABLE_RELEASE)->where('deleted')->eq('0');
        if($product) $sql->andWhere('product')->eq($product);
        $release = $sql->orderBy('date_desc, id_desc')->limit(1)->fetch();
        return $release ? $release->name : 'none';
    }

    /**
     * Build a shields.io-style SVG badge.
     *
     * @param  string $label
     * @param  string $value
     * @param  string $color
     * @access private
     * @return string
     */
    private function svg($label, $value, $color)
    {
        $label = (string)$label;
        $value = (string)$value;

        $labelWidth = strlen($label) * 7 + 10;
        $valueWidth = strlen($value) * 7 + 10;
        $width      = $labelWidth + $valueWidth;

        $labelText = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $valueText = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $altText   = htmlspecialchars($label . ': ' . $value, ENT_QUOTES, 'UTF-8');

        $svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="20" role="img" aria-label="' . $altText . '">';
        $svg .= '<title>' . $altText . '</title>';
        $svg .= '<rect width="' . $labelWidth . '" height="20" fill="#555"/>';
        $svg .= '<rect x="' . $labelWidth . '" width="' . $valueWidth . '" height="20" fill="' . $color . '"/>';
        $svg .= '<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">';
        $svg .= '<text x="' . ($labelWidth / 2) . '" y="14">' . $labelText . '</text>';
        $svg .= '<text x="' . ($labelWidth + $valueWidth / 2) . '" y="14">' . $valueText . '</text>';
        $svg .= '</g></svg>';

        return $svg;
    }
}
