<!-- Modal -->
<div class="modal fade" id="terms-and-conditions-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Managex Terms & Conditions</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <h1>SOFTWARE LICENCE AGREEMENT</h1>

            <p>This Software License Agreement ("Agreement") is entered into between Kingsoft Company Limited ("Licensor") and you ("Licensee") for the use of the Managex software ("Software").</p>
            
            <ol>
                <li>
                    <h3>LICENSE GRANT</h3>
                    <p>Licensor hereby grants Licensee a non-exclusive, non-transferable license to use the Software, subject to the terms and conditions of this Agreement.</p>
                </li>
                <li>
                    <h3>SOFTWARE USE</h3>
                    <p>Licensee may install and use the Software on a single computer or device (Managex Bronze and Silver, and maximum 3 computers for Managex Gold). Licensee agrees not to copy, modify, distribute, sell, or sublicense the Software.</p>
                </li>
                <li>
                    <h3>INTELLECTUAL PROPERTY</h3>
                    <p>The Software and all related intellectual property rights are owned by Licensor and protected by applicable copyright, patent, trademark, and other intellectual property laws.</p>
                </li>
                <li>
                    <h3>DATA USAGE</h3>
                    <p>Licensee acknowledges and agrees that Licensor holds the data in ultimate security and privacy and analyzes data generated through the use of the Software for the purpose of improving performance and providing insights. Licensee's personal data will be handled in accordance with Licensor's privacy policy.</p>
                </li>
                <li>
                    <h3>CLOUD BACKUP</h3>
                    <p>Licensee may opt to use online backup services for storing data generated by the Software. Licensee understands that cloud backup services may incur additional costs for storage space and agrees to pay any applicable fees.</p>
                </li>
                <li>
                    <h3>DISCLAIMER OF WARRANTY</h3>
                    <p>THE SOFTWARE IS PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED. LICENSOR DISCLAIMS ALL WARRANTIES, INCLUDING BUT NOT LIMITED TO MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT</p>
                </li>
                <li>
                    <h3>LIMITATION OF LIABILITY</h3>
                    <p>IN NO EVENT SHALL LICENSOR BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, OR CONSEQUENTIAL DAMAGES ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THE SOFTWARE, EVEN IF LICENSOR HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES</p>
                </li>
                <li>
                    <h3>TERMINATION</h3>
                    <p>Licensor reserves the right to terminate this Agreement at any time if Licensee breaches any term or condition herein. Upon termination, Licensee must cease all use of the Software and destroy all copies in their possession.</p>
                </li>
                <li>
                    <h3>GOVERNING LAW</h3>
                    <p>This Agreement shall be governed by and construed in accordance with the laws of [Jurisdiction]. Any dispute arising under or in connection with this Agreement shall be subject to the exclusive jurisdiction of the courts in [Jurisdiction].</p>
                </li>
                <li>
                    <h3>ENTIRE AGREEMENT</h3>
                    <p>This Agreement constitutes the entire agreement between the parties concerning the subject matter herein and supersedes all prior or contemporaneous agreements or understandings, written or oral.</p>
                </li>
            </ol>
            <p>BY INSTALLING, COPYING, OR OTHERWISE USING THE SOFTWARE, LICENSEE ACKNOWLEDGES THAT THEY HAVE READ AND UNDERSTOOD THIS AGREEMENT AND AGREE TO BE BOUND BY ITS TERMS AND CONDITIONS</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" data-dismiss="modal" @click="()=>{
                $dispatch('agree-license')
            }">Agree</button>
        </div>
        </div>
    </div>
</div>