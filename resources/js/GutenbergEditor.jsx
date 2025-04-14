import React from 'react';
import { render } from '@wordpress/element';
import {
    BlockEditorProvider,
    BlockList,
    WritingFlow,
    ObserveTyping,
    Toolbar,
} from '@wordpress/block-editor';

import { useState } from 'react';
import '@wordpress/components/build-style/style.css';
import '@wordpress/block-editor/build-style/style.css';
import '@wordpress/block-library/style.css'; // ✅ ফিক্সড path


const GutenbergEditor = () => {
    const [blocks, updateBlocks] = useState([]);

    return (
        <BlockEditorProvider
            value={blocks}
            onInput={updateBlocks}
            onChange={updateBlocks}
        >
            <div className="editor-styles-wrapper" style={{ border: '1px solid #ccc', padding: '10px' }}>
                <Toolbar />
                <WritingFlow>
                    <ObserveTyping>
                        <BlockList />
                    </ObserveTyping>
                </WritingFlow>
            </div>
        </BlockEditorProvider>
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('gutenberg-editor');
    if (root) {
        render(<GutenbergEditor />, root);
    }
});
