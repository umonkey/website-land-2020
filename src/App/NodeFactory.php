<?php

namespace App;

class NodeFactory extends \Ufw1\NodeFactory
{
    public function save(array $node)
    {
        if ($node['type'] == 'blog') {
            $node['key'] = $this->urlToKey($node['url'] ?? null);
        }

        elseif ($node['type'] == 'article') {
            $node['key'] = $this->urlToKey($node['url'] ?? null);
        }

        return parent::save($node);
    }

    protected function urlToKey($url)
    {
        if (empty($url))
            return null;

        $url = rtrim($url, ' /');
        $key = md5($url);

        return $key;
    }
}
