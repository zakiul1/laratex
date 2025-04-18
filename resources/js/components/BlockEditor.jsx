import React, { useEffect, useState } from 'react';
import { registerCoreBlocks } from '@wordpress/block-library';
import { parse, serialize } from '@wordpress/blocks';
import { BlockEditorProvider, BlockList, BlockTools } from '@wordpress/block-editor';

export default function BlockEditor({ initialContent, onChange }) {
  const [blocks, setBlocks] = useState([]);

  useEffect(() => {
    registerCoreBlocks();
    const parsed = parse(initialContent || '');
    setBlocks(parsed);
    onChange(serialize(parsed));
  }, [initialContent]);

  return (
    <BlockEditorProvider
      value={blocks}
      onInput={(newBlocks) => {
        setBlocks(newBlocks);
        onChange(serialize(newBlocks));
      }}
      onChange={(newBlocks) => {
        setBlocks(newBlocks);
        onChange(serialize(newBlocks));
      }}
    >
      <BlockTools>
        <div className="editor-container min-h-[200px] border rounded p-4">
          <BlockList />
        </div>
      </BlockTools>
    </BlockEditorProvider>
  );
}